<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h,
	cs\Page\Includes,
	cs\Page\Meta;

/**
 * @method static $this instance($check = false)
 */
class Page {
	use
		Singleton,
		Includes;
	const INIT_STATE_METHOD = 'init';
	/**
	 * Complete page contents
	 *
	 * @var string
	 */
	public $Content;
	/**
	 * If `false` - only page content will be shown, without the rest of interface (useful for AJAX request, though for API it is set to `false` automatically)
	 *
	 * @var bool
	 */
	public $interface;
	/**
	 * @var string
	 */
	public $pre_Html;
	/**
	 * @var string
	 */
	public $Html;
	/**
	 * @var string
	 */
	public $Description;
	/**
	 * @var string|string[]
	 */
	public $Title;
	/**
	 * @var string
	 */
	public $Head;
	/**
	 * @var string
	 */
	public $pre_Body;
	/**
	 * @var string
	 */
	public $Left;
	/**
	 * @var string
	 */
	public $Top;
	/**
	 * @var string
	 */
	public $Right;
	/**
	 * @var string
	 */
	public $Bottom;
	/**
	 * @var string
	 */
	public $post_Body;
	/**
	 * @var string
	 */
	public $post_Html;
	/**
	 * Number of tabs by default for indentation the substitution of values into template
	 *
	 * @var array
	 */
	public $level;
	/**
	 * @var array[]
	 */
	protected $link;
	/**
	 * @var string[]
	 */
	protected $search_replace;
	/**
	 * @var false|string
	 */
	protected $canonical_url;
	protected $theme;
	protected $finish_called_once;
	/**
	 * @param string $property
	 *
	 * @return false|null|string
	 */
	function __get ($property) {
		// Hack: for internal use by \cs\Meta class
		if ($property === 'canonical_url') {
			return $this->canonical_url;
		}
		return false;
	}
	protected function init () {
		$this->Content            = '';
		$this->interface          = true;
		$this->pre_Html           = '';
		$this->Html               = '';
		$this->Description        = '';
		$this->Title              = [];
		$this->Head               = '';
		$this->pre_Body           = '';
		$this->Left               = '';
		$this->Top                = '';
		$this->Right              = '';
		$this->Bottom             = '';
		$this->post_Body          = '';
		$this->post_Html          = '';
		$this->level              = [
			'Head'      => 0,
			'pre_Body'  => 1,
			'Left'      => 3,
			'Top'       => 3,
			'Content'   => 4,
			'Bottom'    => 3,
			'Right'     => 3,
			'post_Body' => 1
		];
		$this->link               = [];
		$this->search_replace     = [];
		$this->canonical_url      = false;
		$this->theme              = null;
		$this->finish_called_once = false;
		$this->init_includes();
		$Config = Config::instance(true);
		/**
		 * We need Config for initialization
		 */
		if (!$Config) {
			Event::instance()->once(
				'System/Config/init/after',
				function () {
					$this->init_config();
				}
			);
		} else {
			$this->init_config();
		}
		Event::instance()->on(
			'System/Config/changed',
			function () {
				$this->init_config();
			}
		);
	}
	/**
	 * Initialization: setting of title and theme according to system configuration
	 *
	 * @todo Probably, make public and add resetting here in order to avoid complete object recreation
	 *
	 * @return Page
	 */
	protected function init_config () {
		$Config         = Config::instance();
		$this->Title[0] = htmlentities(get_core_ml_text('name'), ENT_COMPAT, 'utf-8');
		$this->set_theme($Config->core['theme']);
		return $this;
	}
	/**
	 * Theme changing
	 *
	 * @param string $theme
	 *
	 * @return Page
	 */
	function set_theme ($theme) {
		$this->theme = $theme;
		return $this;
	}
	/**
	 * Adding of content on the page
	 *
	 * @param string   $add
	 * @param bool|int $level
	 *
	 * @return Page
	 */
	function content ($add, $level = false) {
		if ($level !== false) {
			$this->Content .= h::level($add, $level);
		} else {
			$this->Content .= $add;
		}
		return $this;
	}
	/**
	 * Sets body with content, that is transformed into JSON format
	 *
	 * @param mixed $add
	 *
	 * @return Page
	 */
	function json ($add) {
		Response::instance()->header('content-type', 'application/json; charset=utf-8');
		$this->interface = false;
		$this->Content   = _json_encode($add);
		return $this;
	}
	/**
	 * Loading of theme template
	 */
	protected function get_template () {
		/**
		 * Theme is fixed for administration, and may vary for other pages
		 */
		if (Request::instance()->admin_path) {
			$this->theme = 'CleverStyle';
		}
		$theme_dir = THEMES."/$this->theme";
		_include("$theme_dir/prepare.php", false, false);
		ob_start();
		_include("$theme_dir/index.php", false, false) || _include("$theme_dir/index.html");
		$this->Html = ob_get_clean();
	}
	/**
	 * Processing of template, substituting of content, preparing for the output
	 *
	 * @return Page
	 */
	protected function prepare () {
		$Config = Config::instance(true);
		/**
		 * Loading of template
		 */
		$this->get_template();
		/**
		 * Forming page title
		 */
		$this->Title = array_filter($this->Title, 'trim');
		$this->Title = $Config->core['title_reverse'] ? array_reverse($this->Title) : $this->Title;
		$this->Title = implode($Config->core['title_delimiter'] ?: '|', $this->Title);
		/**
		 * Forming <head> content
		 */
		$this->Head =
			h::title($this->Title).
			h::meta(
				[
					'charset' => 'utf-8'
				]
			).
			h::meta(
				$this->Description ? [
					'name'    => 'description',
					'content' => $this->Description
				] : false
			).
			h::meta(
				[
					'name'    => 'generator',
					'content' => 'CleverStyle CMS by Mokrynskyi Nazar'
				]
			).
			h::base(
				$Config ? [
					'href' => $Config->base_url().'/'
				] : false
			).
			$this->Head.
			h::link(
				[
					'rel'  => 'shortcut icon',
					'href' => $this->get_favicon_path()
				]
			).
			h::link(array_values($this->link) ?: false);
		/**
		 * Addition of CSS, JavaScript and Web Components includes
		 */
		$this->add_includes_on_page();
		/**
		 * Generation of Open Graph protocol information
		 */
		Meta::instance()->render();
		/**
		 * Substitution of information into template
		 */
		$this->Html = str_replace(
			[
				'<!--pre_Html-->',
				'<!--head-->',
				'<!--pre_Body-->',
				'<!--left_blocks-->',
				'<!--top_blocks-->',
				'<!--content-->',
				'<!--bottom_blocks-->',
				'<!--right_blocks-->',
				'<!--post_Body-->',
				'<!--post_Html-->'
			],
			_rtrim(
				[
					$this->pre_Html,
					$this->get_property_with_indentation('Head'),
					$this->get_property_with_indentation('pre_Body'),
					$this->get_property_with_indentation('Left'),
					$this->get_property_with_indentation('Top'),
					$this->get_property_with_indentation('Content'),
					$this->get_property_with_indentation('Bottom'),
					$this->get_property_with_indentation('Right'),
					$this->get_property_with_indentation('post_Body'),
					$this->post_Html
				],
				"\t"
			),
			$this->Html
		);
		return $this;
	}
	/**
	 * @return string
	 */
	protected function get_favicon_path () {
		$file = file_exists_with_extension(THEMES."/$this->theme/img/favicon", ['png', 'ico']);
		if ($file) {
			return str_replace(THEMES, 'themes', $file);
		}
		return 'favicon.ico';
	}
	/**
	 * @param string $property
	 *
	 * @return string
	 */
	protected function get_property_with_indentation ($property) {
		return h::level($this->$property, $this->level[$property]);
	}
	/**
	 * Replacing anything in source code of finally generated page
	 *
	 * Parameters may be both simply strings for str_replace() and regular expressions for preg_replace()
	 *
	 * @param string|string[] $search
	 * @param string|string[] $replace
	 *
	 * @return Page
	 */
	function replace ($search, $replace = '') {
		if (is_array($search)) {
			$this->search_replace = $search + $this->search_replace;
		} else {
			/** @noinspection OffsetOperationsInspection */
			$this->search_replace[$search] = $replace;
		}
		return $this;
	}
	/**
	 * Processing of replacing in content
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	protected function process_replacing ($content) {
		foreach ($this->search_replace as $search => $replace) {
			$content = _preg_replace($search, $replace, $content) ?: str_replace($search, $replace, $content);
		}
		$this->search_replace = [];
		return $content;
	}
	/**
	 * Adding links
	 *
	 * @param array $data According to h class syntax
	 *
	 * @return Page
	 */
	function link ($data) {
		if ($data !== false) {
			$this->link[] = $data;
		}
		return $this;
	}
	/**
	 * Simple wrapper of $Page->link() for inserting Atom feed on page
	 *
	 * @param string $href
	 * @param string $title
	 *
	 * @return Page
	 */
	function atom ($href, $title = 'Atom Feed') {
		return $this->link(
			[
				'href'  => $href,
				'title' => $title,
				'rel'   => 'alternate',
				'type'  => 'application/atom+xml'
			]
		);
	}
	/**
	 * Simple wrapper of $Page->link() for inserting RSS feed on page
	 *
	 * @param string $href
	 * @param string $title
	 *
	 * @return Page
	 */
	function rss ($href, $title = 'RSS Feed') {
		return $this->link(
			[
				'href'  => $href,
				'title' => $title,
				'rel'   => 'alternate',
				'type'  => 'application/rss+xml'
			]
		);
	}
	/**
	 * Specify canonical url of current page
	 *
	 * @param string $url
	 *
	 * @return Page
	 */
	function canonical_url ($url) {
		$this->canonical_url         = $url;
		$this->link['canonical_url'] = [
			'href' => $this->canonical_url,
			'rel'  => 'canonical'
		];
		return $this;
	}
	/**
	 * Adding text to the title page
	 *
	 * @param string $title
	 * @param bool   $replace Replace whole title by this
	 *
	 * @return Page
	 */
	function title ($title, $replace = false) {
		$title = htmlentities($title, ENT_COMPAT, 'utf-8');
		if ($replace) {
			$this->Title = [$title];
		} else {
			$this->Title[] = $title;
		}
		return $this;
	}
	/**
	 * Display success message
	 *
	 * @param string $success_text
	 *
	 * @return Page
	 */
	function success ($success_text) {
		return $this->top_message($success_text, 'success');
	}
	/**
	 * Display notice message
	 *
	 * @param string $notice_text
	 *
	 * @return Page
	 */
	function notice ($notice_text) {
		return $this->top_message($notice_text, 'warning');
	}
	/**
	 * Display warning message
	 *
	 * @param string $warning_text
	 *
	 * @return Page
	 */
	function warning ($warning_text) {
		return $this->top_message($warning_text, 'error');
	}
	/**
	 * Generic method for 3 methods above
	 *
	 * @param string $message
	 * @param string $class_ending
	 *
	 * @return Page
	 */
	protected function top_message ($message, $class_ending) {
		$this->Top .= h::div(
			$message,
			[
				'class' => "cs-text-center cs-block-$class_ending cs-text-$class_ending"
			]
		);
		return $this;
	}
	/**
	 * Error pages processing
	 *
	 * @param null|string|string[] $custom_text Custom error text instead of text like "404 Not Found" or array with two elements: [error, error_description]
	 * @param bool                 $json        Force JSON return format
	 */
	function error ($custom_text = null, $json = false) {
		$Request    = Request::instance();
		$Response   = Response::instance();
		$error_code = $Response->code;
		/**
		 * Hack for 403 after sign out in administration
		 */
		if ($error_code == 403 && !$Request->api_path && $Request->cookie('sign_out')) {
			$Response->redirect('/');
			$this->Content = '';
			return;
		}
		$status_text       = status_code_string($error_code);
		$error_description = $status_text;
		if (is_array($custom_text)) {
			list($status_text, $error_description) = $custom_text;
		} elseif ($custom_text) {
			$error_description = $custom_text;
		}
		if ($json || $Request->api_path) {
			$this->json(
				[
					'error'             => $error_code,
					'error_description' => $error_description
				]
			);
		} else {
			ob_start();
			if (
				!_include(THEMES."/$this->theme/error.html", false, false) &&
				!_include(THEMES."/$this->theme/error.php", false, false)
			) {
				echo
					"<!doctype html>\n".
					h::title($status_text).
					$error_description;
			}
			$this->Content = ob_get_clean();
		}
		$Response->body = $this->Content;
	}
	/**
	 * @deprecated Use `cs\Page::render()` instead
	 */
	function __finish () {
		$this->render();
	}
	/**
	 * Provides next events:
	 *  System/Page/render/before
	 *
	 *  System/Page/render/after
	 *
	 * Page generation
	 */
	function render () {
		/**
		 * Protection from double calling
		 */
		if ($this->finish_called_once) {
			return;
		}
		$this->finish_called_once = true;
		$Response                 = Response::instance();
		if (is_resource($Response->body_stream)) {
			return;
		}
		/**
		 * For AJAX and API requests only content without page template
		 */
		$api_path = Request::instance()->api_path;
		if ($api_path || !$this->interface) {
			/**
			 * Processing of replacing in content
			 */
			/** @noinspection NestedTernaryOperatorInspection */
			$Response->body = $this->process_replacing($this->Content ?: ($api_path ? 'null' : ''));
		} else {
			Event::instance()->fire('System/Page/display/before');
			Event::instance()->fire('System/Page/render/before');
			/**
			 * Processing of template, substituting of content, preparing for the output
			 */
			$this->prepare();
			/**
			 * Processing of replacing in content
			 */
			$this->Html = $this->process_replacing($this->Html);
			Event::instance()->fire('System/Page/display/after');
			Event::instance()->fire('System/Page/render/after');
			$Response->body = rtrim($this->Html);
		}
	}
}
