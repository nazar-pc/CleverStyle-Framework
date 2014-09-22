<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use
	h,
	cs\Page\Includes_processing;

/**
 * Provides next triggers:<br>
 *  System/Page/pre_display
 *  System/Page/get_header_info
 *  System/Page/rebuild_cache
 *  ['key'	=> &$key]		//Reference to the key, that will be appended to all css and js files, can be changed to reflect JavaScript and CSS changes
 *  System/Page/external_sign_in_list
 *  ['list'	=> &$list]		//Reference to the list of external sign in systems
 *
 * @method static Page instance($check = false)
 */
class Page {
	use	Singleton;
	public	$Content;
	public	$interface		= true;
	public	$pre_Html		= '';
	public	$Html 			= '';
	public		$Description	= '';
	public		$Title			= [];
	public	$debug_info		= '';
	public	$Head			= '';
	public	$pre_Body		= '';
	public		$Header		= '';
	public			$main_menu		= '';
	public			$main_sub_menu	= '';
	public			$main_menu_more	= '';
	public		$Left		= '';
	public		$Top		= '';
	public		$Right		= '';
	public		$Bottom		= '';
	public		$Footer		= '';
	public	$post_Body		= '';
	public	$post_Html		= '';
	/**
	 * Number of tabs by default for indentation the substitution of values into template
	 * @var array
	 */
	public	$level				= [
		'Head'				=> 0,
		'pre_Body'			=> 1,
		'Header'			=> 3,
		'main_menu'			=> 3,
		'main_sub_menu'		=> 3,
		'main_menu_more'	=> 3,
		'header_info'		=> 4,
		'debug_info'		=> 2,
		'Left'				=> 3,
		'Top'				=> 3,
		'Content'			=> 4,
		'Bottom'			=> 3,
		'Right'				=> 3,
		'Footer'			=> 2,
		'post_Body'			=> 1
	];
	public	$user_avatar_image;
	public	$header_info;
	/**
	 * Is used as <head prefix="$head_prefix">
	 * @var string
	 */
	public	$head_prefix		= '';
	/**
	 * If false - &lt;head&gt; will not be added automatically, and should be in template if needed
	 * @var bool
	 */
	public	$no_head		= false;
	public	$core_html		= [0 => [], 1 => ''];
	public	$core_js		= [0 => [], 1 => []];
	public	$core_css		= [0 => [], 1 => []];
	public	$html			= [0 => [], 1 => ''];
	public	$js				= [0 => [], 1 => []];
	public	$css			= [0 => [], 1 => []];
	public	$link			= [];
	public	$Search			= [];
	public	$Replace		= [];
	public	$og_data		= [];
	public	$og_type		= '';
	public	$canonical_url	= false;
	protected	$theme, $color_scheme, $pcache_basename;
	/**
	 * Initialization: setting of title, theme and color scheme according to specified parameters
	 *
	 * @param string	$title
	 * @param string	$theme
	 * @param string	$color_scheme
	 *
	 * @return Page
	 */
	function init ($title, $theme, $color_scheme) {
		$this->Title[0] = htmlentities($title, ENT_COMPAT, 'utf-8');
		$this->set_theme($theme);
		$this->set_color_scheme($color_scheme);
		return $this;
	}
	/**
	 * Theme changing
	 *
	 * @param string	$theme
	 *
	 * @return Page
	 */
	function set_theme ($theme) {
		$this->theme = $theme;
		return $this;
	}
	/**
	 * Color scheme changing
	 *
	 * @param string	$color_scheme
	 *
	 * @return Page
	 */
	function set_color_scheme ($color_scheme) {
		$this->color_scheme = $color_scheme;
		return $this;
	}
	/**
	 * Adding of content on the page
	 *
	 * @param string	$add
	 * @param bool|int	$level
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
	 * @param mixed	$add
	 *
	 * @return Page
	 */
	function json ($add) {
		if (!api_path()) {
			header('Content-Type: application/json; charset=utf-8', true);
			interface_off();
		}
		$this->Content	= _json_encode($add);
		return $this;
	}
	/**
	 * Loading of theme template
	 *
	 * @return Page
	 */
	protected function get_template () {
		$Config					= Config::instance();
		/**
		 * Theme detection
		 */
		if (is_object($Config)) {
			$this->theme		= in_array($this->theme, $Config->core['themes']) ? $this->theme : $Config->core['theme'];
			$this->color_scheme	= in_array($this->color_scheme, $Config->core['color_schemes'][$this->theme]) ?
									$this->color_scheme : $Config->core['color_schemes'][$this->theme][0];
		}
		/**
		 * Base name for cache files
		 */
		$this->pcache_basename	= "_{$this->theme}_{$this->color_scheme}_".Language::instance()->clang;
		/**
		 * Template loading
		 */
		$theme_dir				= THEMES."/$this->theme";
		if ($this->interface) {
			_include_once("$theme_dir/prepare.php", false);
			ob_start();
			if (
				!(
					file_exists("$theme_dir/index.html") || file_exists("$theme_dir/index.php")
				) ||
				(
					!(
						is_object($Config) && $Config->core['site_mode']
					) &&
					!User::instance(true)->admin() &&
					code_header(503) &&
					!(
						_include_once("$theme_dir/closed.php", false) || _include_once("$theme_dir/closed.html", false)
					)
				)
			) {
				echo	"<!doctype html>\n".
				h::title(get_core_ml_text('closed_title')).
				get_core_ml_text('closed_text');
			} else {
				_include_once("$theme_dir/index.php", false) || _include_once("$theme_dir/index.html");
			}
			$this->Html = ob_get_clean();
		}
		return $this;
	}
	/**
	 * Processing of template, substituting of content, preparing for the output
	 *
	 * @return Page
	 */
	protected function prepare () {
		$Config				= Config::instance(true);
		/**
		 * Loading of template
		 * Loading of CSS and JavaScript
		 * Getting user information
		 */
		$this->get_template()->add_includes_on_page()->get_header_info();
		/**
		 * Forming page title
		 */
		foreach ($this->Title as $i => $v) {
			if (!trim($v)) {
				unset($this->Title[$i]);
			} else {
				$this->Title[$i] = trim($v);
			}
		}
		if ($Config) {
			$this->Title = $Config->core['title_reverse'] ? array_reverse($this->Title) : $this->Title;
			$this->Title = implode($Config->core['title_delimiter'], $this->Title);
		} else {
			$this->Title = $this->Title[0];
		}
		/**
		 * Core JS
		 */
		if ($Config) {
			$Index	= Index::instance();
			$User	= User::instance();
			$this->js_internal(
				'window.cs	= '._json_encode([
					'base_url'			=> $Config->base_url(),
					'current_base_url'	=> $Config->base_url().'/'.($Index->in_admin() ? 'admin/' : '').current_module(),
					'public_key'		=> Core::instance()->public_key,
					'module'			=> current_module(),
					'in_admin'			=> (int)$Index->in_admin(),
					'is_admin'			=> (int)$User->admin(),
					'is_user'			=> (int)$User->user(),
					'is_guest'			=> (int)$User->guest(),
					'debug'				=> (int)$User->guest(),
					'cookie_prefix'		=> $Config->core['cookie_prefix'],
					'cookie_domain'		=> $Config->core['cookie_domain'][$Config->server['mirror_index']],
					'cookie_path'		=> $Config->core['cookie_path'][$Config->server['mirror_index']],
					'protocol'			=> $Config->server['protocol'],
					'route'				=> $Config->route,
					'route_path'		=> $Index->route_path,
					'route_ids'			=> $Index->route_ids
				]).';',
				'code',
				true
			);
			if ($User->guest()) {
				$this->js(
					'cs.rules_text = '._json_encode(get_core_ml_text('rules')).';',
					'code'
				);
			}
			if (!$Config->core['cache_compress_js_css']) {
				$this->js(
					'cs.Language = '._json_encode(Language::instance()).';',
					'code'
				);
			}
		}
		/**
		 * Forming <head> content
		 */
		$this->core_html[0]	= implode('', array_unique($this->core_html[0]));
		$this->html[0]		= implode('', array_unique($this->html[0]));
		$this->core_css[0]	= implode('', array_unique($this->core_css[0]));
		$this->core_css[1]	= implode('', array_unique($this->core_css[1]));
		$this->css[0]		= implode('', array_unique($this->css[0]));
		$this->css[1]		= implode('', array_unique($this->css[1]));
		$this->core_js[0]	= implode('', array_unique($this->core_js[0]));
		$this->core_js[1]	= implode('', array_unique($this->core_js[1]));
		$this->js[0]		= implode('', array_unique($this->js[0]));
		$this->js[1]		= implode('', array_unique($this->js[1]));
		if (file_exists(THEMES."/$this->theme/$this->color_scheme/img/favicon.png")) {
			$favicon	= "themes/$this->theme/$this->color_scheme/img/favicon.png";
		} elseif (file_exists(THEMES."/$this->theme/$this->color_scheme/img/favicon.ico")) {
			$favicon	= "themes/$this->theme/$this->color_scheme/img/favicon.ico";
		} elseif (file_exists(THEMES."/$this->theme/img/favicon.png")) {
			$favicon	= "themes/$this->theme/img/favicon.png";
		} elseif (file_exists(THEMES."/$this->theme/img/favicon.ico")) {
			$favicon	= "themes/$this->theme/img/favicon.ico";
		} else {
			$favicon	= 'favicon.ico';
		}
		$this->Head			=	h::title($this->Title).
			h::meta(
				[
					'charset'		=> 'utf-8'
				],
				$this->Description ? [
					'name'			=> 'description',
					'content'		=> $this->Description
				] : false,
				[
					'name'			=> 'generator',
					'content'		=> base64_decode('Q2xldmVyU3R5bGUgQ01TIGJ5IE1va3J5bnNreWkgTmF6YXI=')
				],
				admin_path() ? [
					'name'			=> 'robots',
					'content'		=> 'noindex,nofollow'
				] : false
			).
			h::base($Config ? [
				'href' => $Config->base_url().'/'
			] : false).
			$this->Head.
			h::link(
				[
					[
						'rel'		=> 'shortcut icon',
						'href'		=> $favicon
					]
				],
				$this->link ?: false
			).
			$this->core_css[0].$this->css[0].
			h::style($this->core_css[1].$this->css[1] ?: false).
			h::script($this->core_js[1].$this->js[1]);
		if ($Config->core['put_js_after_body']) {
			$this->post_Body	.=
				$this->core_js[0].$this->js[0].
				$this->core_html[0].$this->html[0].
				$this->core_html[1].$this->html[1];
		} else {
			$this->Head			.=
				$this->core_js[0].$this->js[0].
				$this->core_html[0].$this->html[0].
				$this->core_html[1].$this->html[1];
		}
		/**
		 * Generation of Open Graph protocol information
		 */
		$this->og_generation();
		/**
		 * Getting footer information
		 */
		$this->get_footer();
		/**
		 * Menu generation
		 */
		$Index				= Index::instance();
		if (!$this->main_menu && $Index->main_menu) {
			$this->main_menu	= h::{'li| a'}($Index->main_menu);
		}
		if ($Index->main_sub_menu) {
			if (!$this->main_sub_menu) {
				foreach ($Index->main_sub_menu as $item) {
					if (isset($item[1], $item[1]['class']) && $item[1]['class'] == 'uk-active') {
						if ($Index->main_menu_more) {
							$item[0]				.= ' '.h::icon('caret-down');
						}
						$item[1]['class']		= trim(str_replace('uk-active', '', $item[1]['class']));
						$this->main_sub_menu	.= h::{'li.uk-active[data-uk-dropdown=]'}(
							h::a($item).
							(
								$Index->main_menu_more ? h::{'div.uk-dropdown.uk-dropdown-small ul.uk-nav.uk-nav-dropdown li| a'}($Index->main_menu_more) : ''
							)
						);
					} else {
						$this->main_sub_menu	.= h::{'li a'}($item);
					}
				}
			}
		} elseif (!$this->main_menu && $Index->main_menu_more) {
			$this->main_menu	= h::{'li| a'}($Index->main_menu_more);
		}
		/**
		 * Substitution of information into template
		 */
		$this->Html			= str_replace(
			[
				'<!--pre_Html-->',
				'<!--head-->',
				'<!--pre_Body-->',
				'<!--header-->',
				'<!--main-menu-->',
				'<!--main-sub-menu-->',
				'<!--main-menu-more-->',
				'<!--user-avatar-image-->',
				'<!--header_info-->',
				'<!--left_blocks-->',
				'<!--top_blocks-->',
				'<!--content-->',
				'<!--bottom_blocks-->',
				'<!--right_blocks-->',
				'<!--footer-->',
				'<!--post_Body-->',
				'<!--post_Html-->'
			],
			[
				$this->pre_Html,
				h::level($this->Head, $this->level['Head']),
				h::level($this->pre_Body, $this->level['pre_Body']),
				h::level($this->Header, $this->level['Header']),
				h::level($this->main_menu, $this->level['main_menu']),
				h::level($this->main_sub_menu, $this->level['main_sub_menu']),
				h::level($this->main_menu_more, $this->level['main_menu_more']),
				$this->user_avatar_image,
				h::level($this->header_info, $this->level['header_info']),
				h::level($this->Left, $this->level['Left']),
				h::level($this->Top, $this->level['Top']),
				h::level($this->Content, $this->level['Content']),
				h::level($this->Bottom, $this->level['Bottom']),
				h::level($this->Right, $this->level['Right']),
				h::level($this->Footer, $this->level['Footer']),
				h::level($this->post_Body, $this->level['post_Body']),
				$this->post_Html
			],
			$this->Html
		);
		return $this;
	}
	/**
	 * Replacing anything in source code of finally generated page
	 *
	 * Parameters may be both simply strings for str_replace() and regular expressions for preg_replace()
	 *
	 * @param string|string[]	$search
	 * @param string|string[]	$replace
	 *
	 * @return Page
	 */
	function replace ($search, $replace = '') {
		if (is_array($search)) {
			foreach ($search as $i => $val) {
				$this->Search[] = $val;
				$this->Replace[] = is_array($replace) ? $replace[$i] : $replace;
			}
		} else {
			$this->Search[] = $search;
			$this->Replace[] = $replace;
		}
		return $this;
	}
	/**
	 * Processing of replacing in content
	 *
	 * @param string	$data
	 *
	 * @return string
	 */
	protected function process_replacing ($data) {
		errors_off();
		foreach ($this->Search as $i => $search) {
			$data = _preg_replace($search, $this->Replace[$i], $data) ?: str_replace($search, $this->Replace[$i], $data);
		}
		$this->Search  = [];
		$this->Replace = [];
		errors_on();
		return $data;
	}
	/**
	 * Including of Web Components
	 *
	 * @param string|string[]	$add	Path to including file, or code
	 * @param string			$mode	Can be <b>file</b> or <b>code</b>
	 *
	 * @return Page
	 */
	function html ($add, $mode = 'file') {
		return $this->html_internal($add, $mode);
	}
	/**
	 * @param string|string[]	$add
	 * @param string			$mode
	 * @param bool				$core
	 *
	 * @return Page
	 */
	protected function html_internal ($add, $mode = 'file', $core = false) {
		if (is_array($add)) {
			foreach ($add as $script) {
				if ($script) {
					$this->html_internal($script, $mode, $core);
				}
			}
		} elseif ($add) {
			if ($core) {
				if ($mode == 'file') {
					$this->core_html[0][]	= h::link([
						'href'	=> $add,
						'rel'	=> 'import'
					]);
				} elseif ($mode == 'code') {
					$this->core_html[1]	= $add."\n";
				}
			} else {
				if ($mode == 'file') {
					$this->html[0][]		= h::link([
						'href'	=> $add,
						'rel'	=> 'import'
					]);
				} elseif ($mode == 'code') {
					$this->html[1]		= $add."\n";
				}
			}
		}
		return $this;
	}
	/**
	 * Including of JavaScript
	 *
	 * @param string|string[]	$add	Path to including file, or code
	 * @param string			$mode	Can be <b>file</b> or <b>code</b>
	 *
	 * @return Page
	 */
	function js ($add, $mode = 'file') {
		return $this->js_internal($add, $mode);
	}
	/**
	 * @param string|string[]	$add
	 * @param string			$mode
	 * @param bool				$core
	 *
	 * @return Page
	 */
	protected function js_internal ($add, $mode = 'file', $core = false) {
		if (is_array($add)) {
			foreach ($add as $script) {
				if ($script) {
					$this->js_internal($script, $mode, $core);
				}
			}
		} elseif ($add) {
			if ($core) {
				if ($mode == 'file') {
					$this->core_js[0][]	= h::script([
						'src'	=> $add,
						'level'	=> false
					])."\n";
				} elseif ($mode == 'code') {
					$this->core_js[1][]	= $add."\n";
				}
			} else {
				if ($mode == 'file') {
					$this->js[0][]		= h::script([
						'src'	=> $add,
						'level'	=> false
					])."\n";
				} elseif ($mode == 'code') {
					$this->js[1][]		= $add."\n";
				}
			}
		}
		return $this;
	}
	/**
	 * Including of CSS
	 *
	 * @param string|string[]	$add	Path to including file, or code
	 * @param string			$mode	Can be <b>file</b> or <b>code</b>
	 *
	 * @return Page
	 */
	function css ($add, $mode = 'file') {
		return $this->css_internal($add, $mode);
	}
	/**
	 * @param string|string[]	$add
	 * @param string			$mode
	 * @param bool				$core
	 *
	 * @return Page
	 */
	protected function css_internal ($add, $mode = 'file', $core = false) {
		if (is_array($add)) {
			foreach ($add as $style) {
				if ($style) {
					$this->css_internal($style, $mode, $core);
				}
			}
		} elseif ($add) {
			if ($core) {
				if ($mode == 'file') {
					$this->core_css[0][]	= h::link([
						'href'	=> $add,
						'rel'	=> 'stylesheet'
					]);
				} elseif ($mode == 'code') {
					$this->core_css[1][]	 = $add."\n";
				}
			} else {
				if ($mode == 'file') {
					$this->css[0][]			= h::link([
						'href'	=> $add,
						'rel'	=> 'stylesheet'
					]);
				} elseif ($mode == 'code') {
					$this->css[1][]			 = $add."\n";
				}
			}
		}
		return $this;
	}
	/**
	 * Adding links
	 *
	 * @param array	$data	According to h class syntax
	 *
	 * @return Page
	 */
	function link ($data) {
		if ($data !== false) {
			$this->link[]	= [$data];
		}
		return $this;
	}
	/**
	 * Simple wrapper of $Page->link() for inserting Atom feed on page
	 *
	 * @param string    $href
	 * @param string    $title
	 *
	 * @return Page
	 */
	function atom ($href, $title = 'Atom Feed') {
		return $this->link([
			'href'	=> $href,
			'title'	=> $title,
			'rel'	=> 'alternate',
			'type'	=> 'application/atom+xml'
		]);
	}
	/**
	 * Simple wrapper of $Page->link() for inserting RSS feed on page
	 *
	 * @param string	$href
	 * @param string	$title
	 *
	 * @return Page
	 */
	function rss ($href, $title = 'RSS Feed') {
		return $this->link([
			'href'	=> $href,
			'title'	=> $title,
			'rel'	=> 'alternate',
			'type'	=> 'application/rss+xml'
		]);
	}
	/**
	 * Specify canonical url of current page
	 *
	 * @param string	$url
	 *
	 * @return Page
	 */
	function canonical_url ($url) {
		$this->canonical_url	= $url;
		return $this->link([
			'href'	=> $this->canonical_url,
			'rel'	=> 'canonical'
		]);
	}
	/**
	 * Open Graph protocol support
	 *
	 * Provides automatic addition of &lt;html prefix="og: http://ogp.me/ns#"&gt;, and is used for simplification of Open Graph protocol support
	 *
	 * @param string			$property		Property name, but without <i>og:</i> prefix. For example, <i>title</i>
	 * @param string|string[]	$content		Content, may be an array
	 * @param string			$custom_prefix	If prefix should differ from <i>og:</i>, for example, <i>article:</i> - specify it here
	 *
	 * @return Page
	 */
	function og ($property, $content, $custom_prefix = 'og:') {
		if (!$property || !($content || $content === 0)) {
			return $this;
		}
		if (!Config::instance()->core['og_support']) {
			return $this;
		}
		if (is_array($content)) {
			foreach ($content as $c) {
				$this->og($property, $c, $custom_prefix);
			}
			return $this;
		}
		if (!isset($this->og_data[$property])) {
			$this->og_data[$property]	= '';
		}
		if ($property == 'type') {
			$this->og_type	= $content;
		}
		$this->og_data[$property]	.= h::meta([
			'property'	=> $custom_prefix.$property,
			'content'	=> $content
		]);
		return $this;
	}
	/**
	 * Generates Open Graph protocol information, and puts it into HTML
	 */
	protected function og_generation () {
		/**
		 * Automatic generation of some information
		 */
		$Config		= Config::instance();
		if (!$Config->core['og_support']) {
			return;
		}
		$og			= &$this->og_data;
		if (!isset($og['title']) || empty($og['title'])) {
			$this->og('title', $this->Title);
		}
		if (
			(
				!isset($og['description']) || empty($og['description'])
			) &&
			$this->Description
		) {
			$this->og('description', $this->Description);
		}
		if (!isset($og['url']) || empty($og['url'])) {
			$this->og('url', home_page() ? $Config->base_url() : ($this->canonical_url ?: $Config->base_url().'/'.$Config->server['relative_address']));
		}
		if (!isset($og['site_name']) || empty($og['site_name'])) {
			$this->og('site_name', get_core_ml_text('name'));
		}
		if (!isset($og['type']) || empty($og['type'])) {
			$this->og('type', 'website');
		}
		if ($Config->core['multilingual']) {
			$L	= Language::instance();
			if (!isset($og['locale']) || empty($og['locale'])) {
				$this->og('locale', $L->clocale);
			}
			if (
				(
					!isset($og['locale:alternate']) || empty($og['locale:alternate'])
				) && count($Config->core['active_languages']) > 1
			) {
				foreach ($Config->core['active_languages'] as $lang) {
					if ($lang != $L->clanguage) {
						$this->og('locale:alternate', $L->get('clocale', $lang));
					}
				}
			}
		}
		$prefix		= 'og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#';
		switch (explode('.', $this->og_type, 2)[0]) {
			case 'article':
				$prefix	.= ' article: http://ogp.me/ns/article#';
			break;
			case 'blog':
				$prefix	.= ' blog: http://ogp.me/ns/blog#';
			break;
			case 'book':
				$prefix	.= ' book: http://ogp.me/ns/book#';
			break;
			case 'profile':
				$prefix	.= ' profile: http://ogp.me/ns/profile#';
			break;
			case 'video':
				$prefix	.= ' video: http://ogp.me/ns/video#';
			break;
			case 'website':
				$prefix	.= ' website: http://ogp.me/ns/website#';
			break;
		}
		$this->Head	= $this->Head.implode('', $og);
		if (!$this->no_head) {
			$this->Head	= h::head(
				$this->Head,
				[
					'prefix'	=> $prefix.$this->head_prefix
				]
			);
		}
	}
	/**
	 * Adding text to the title page
	 *
	 * @param string	$add
	 * @param bool		$replace	Replace whole title by this
	 *
	 * @return Page
	 */
	function title ($add, $replace = false) {
		if ($replace) {
			$this->Title	= [htmlentities($add, ENT_COMPAT, 'utf-8')];
		} else {
			$this->Title[]	= htmlentities($add, ENT_COMPAT, 'utf-8');
		}
		return $this;
	}
	/**
	 * Getting of CSS and JavaScript includes
	 *
	 * @return Page
	 */
	protected function add_includes_on_page () {
		if (!($Config = Config::instance(true))) {
			return $this;
		}
		/**
		 * If CSS and JavaScript compression enabled
		 */
		if ($Config->core['cache_compress_js_css']) {
			/**
			 * Current cache checking
			 */
			if (!file_exists(PCACHE."/$this->pcache_basename.json")) {
				$this->rebuild_cache();
			}
			$data				= file_get_json(PCACHE."/$this->pcache_basename.json");
			$structure			= $data['structure'];
			$dependencies		= $data['dependencies'];
			unset($data);
			/**
			 * Narrow the dependence to current module only
			 */
			$dependencies			= isset($dependencies[current_module()]) ? $dependencies[current_module()] : [];
			$system_includes		= [
				'css'	=> ["storage/pcache/$this->pcache_basename.css?{$structure['']['css']}"],
				'js'	=> ["storage/pcache/$this->pcache_basename.js?{$structure['']['js']}"],
				'html'	=> ["storage/pcache/$this->pcache_basename.html?{$structure['']['html']}"]
			];
			$includes				= [
				'css'	=> [],
				'js'	=> [],
				'html'	=> []
			];
			$dependencies_includes	= $includes;
			$current_url			= str_replace('/', '+', $Config->server['relative_address']);
			foreach ($structure as $filename_prefix => $hashes) {
				$prefix_module	= explode('+', $filename_prefix);
				$prefix_module	= $prefix_module[0] != 'admin' ? $prefix_module[0] : $prefix_module[1];
				$is_dependency	= false;
				if (
					(
						$filename_prefix &&
						mb_strpos($current_url, $filename_prefix) === 0
					) ||
					(
						$dependencies &&
						array_search($prefix_module, $dependencies) !== false &&
						$is_dependency = true
					)
				) {
					foreach ($hashes as $extension => $hash) {
						if ($is_dependency) {
							$dependencies_includes[$extension][] = "storage/pcache/$filename_prefix$this->pcache_basename.$extension?$hash";
						} else {
							$includes[$extension][] = "storage/pcache/$filename_prefix$this->pcache_basename.$extension?$hash";
						}
					}
					unset($extension, $hash);
				}
				unset($prefix_module, $is_dependency);
			}
			unset($dependencies, $structure, $filename_prefix, $hashes);
			$this->css_internal(
				array_merge(
					$system_includes['css'],
					$dependencies_includes['css'],
					$includes['css']
				),
				'file',
				true
			);
			$this->js_internal(
				array_merge(
					$system_includes['js'],
					$dependencies_includes['js'],
					$includes['js']
				),
				'file',
				true
			);
			$this->html_internal(
				array_merge(
					$system_includes['html'],
					$dependencies_includes['html'],
					$includes['html']
				),
				'file',
				true
			);
		} else {
			if ($Config) {
				$this->includes_dependencies_and_map($dependencies, $includes_map);
				/**
				 * Add system includes
				 */
				$includes				= [
					'css'	=> [],
					'js'	=> [],
					'html'	=> []
				];
				$dependencies_includes	= $includes;
				$current_url	= $Config->server['relative_address'];
				/**
				 * Narrow the dependence to current module only
				 */
				$dependencies	= isset($dependencies[current_module()]) ? $dependencies[current_module()] : [];
				foreach ($includes_map as $url => $local_includes) {
					if (!$url) {
						continue;
					}
					$prefix_module	= explode('+', $url);
					$prefix_module	= $prefix_module[0] != 'admin' ? $prefix_module[0] : $prefix_module[1];
					$is_dependency	= false;
					if (
						mb_strpos($current_url, $url) === 0 ||
						(
							$dependencies &&
							array_search($prefix_module, $dependencies) !== false &&
							$is_dependency = true
						)
					) {
						if ($is_dependency) {
							if (isset($local_includes['css'])) {
								$dependencies_includes['css'] = array_merge($dependencies_includes['css'], $local_includes['css']);
							}
							if (isset($local_includes['js'])) {
								$dependencies_includes['js'] = array_merge($dependencies_includes['js'], $local_includes['js']);
							}
							if (isset($local_includes['html'])) {
								$dependencies_includes['html'] = array_merge($dependencies_includes['html'], $local_includes['html']);
							}
						} else {
							if (isset($local_includes['css'])) {
								$includes['css'] = array_merge($includes['css'], $local_includes['css']);
							}
							if (isset($local_includes['js'])) {
								$includes['js'] = array_merge($includes['js'], $local_includes['js']);
							}
							if (isset($local_includes['html'])) {
								$includes['html'] = array_merge($includes['html'], $local_includes['html']);
							}
						}
					}
				}
				unset($current_url, $dependencies, $url, $local_includes, $prefix_module, $is_dependency);
				$includes['css']	= array_merge(
					$includes_map['']['css'] ?: [],
					$dependencies_includes['css'] ?: [],
					$includes['css'] ?: []
				);
				$includes['js']		= array_merge(
					$includes_map['']['js'] ?: [],
					$dependencies_includes['js'] ?: [],
					$includes['js'] ?: []
				);
				$includes['html']	= array_merge(
					$includes_map['']['html'] ?: [],
					$dependencies_includes['html'] ?: [],
					$includes['html'] ?: []
				);
				unset($dependencies_includes);
				$root_strlen = strlen(DIR.'/');
				foreach ($includes['css'] as &$file) {
					$file = substr($file, $root_strlen);
				}
				unset($file);
				foreach ($includes['js'] as &$file) {
					$file = substr($file, $root_strlen);
				}
				unset($file);
				foreach ($includes['html'] as &$file) {
					$file = substr($file, $root_strlen);
				}
				unset($root_strlen, $file);
			} else {
				$includes	= $this->get_includes_list();
			}
			/**
			 * Including of CSS
			 */
			$this->css_internal($includes['css'], 'file', true);
			/**
			 * Including of JavaScript
			 */
			$this->js_internal($includes['js'], 'file', true);
			/**
			 * Including of Web Components
			 */
			$this->html_internal($includes['html'], 'file', true);
		}
		return $this;
	}
	/**
	 * Getting of JavaScript and CSS files list to be included
	 *
	 * @param bool		$absolute	If <i>true</i> - absolute paths to files will be returned
	 *
	 * @return array
	 */
	protected function get_includes_list ($absolute = false) {
		$theme_dir		= THEMES."/$this->theme";
		$scheme_dir		= "$theme_dir/schemes/$this->color_scheme";
		$theme_pdir		= "themes/$this->theme";
		$scheme_pdir	= "$theme_pdir/schemes/$this->color_scheme";
		$get_files		= function ($dir, $prefix_path) {
			$extension	= basename($dir);
			$list		= get_files_list(
				$dir,
				"/(.*)\\.$extension/i",
				'f',
				$prefix_path,
				true,
				false,
				'!include'
			) ?: [];
			sort($list);
			return $list;
		};
			/**
		 * Get includes of system and theme + color scheme
		 */
		$includes	= [
			'css' => array_merge(
				$get_files(CSS, $absolute ? true : 'includes/css'),
				$get_files("$theme_dir/css", $absolute ? true : "$theme_pdir/css"),
				$get_files("$scheme_dir/css", $absolute ? true : "$scheme_pdir/css")
			),
			'js' => array_merge(
				$get_files(JS, $absolute ? true : 'includes/js'),
				$get_files("$theme_dir/js", $absolute ? true : "$theme_pdir/js"),
				$get_files("$scheme_dir/js", $absolute ? true : "$scheme_pdir/js")
			),
			'html' => array_merge(
				$get_files(HTML, $absolute ? true : 'includes/html'),
				$get_files("$theme_dir/html", $absolute ? true : "$theme_pdir/html"),
				$get_files("$scheme_dir/html", $absolute ? true : "$scheme_pdir/html")
			)
		];
		unset($theme_dir, $scheme_dir, $theme_pdir, $scheme_pdir);
		$Config		= Config::instance();
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == -1) {
				continue;
			}
			$includes['css']	= array_merge(
				$includes['css'],
				$get_files(MODULES."/$module_name/includes/css", $absolute ? true : "components/modules/$module_name/includes/css")
			);
			$includes['js']		= array_merge(
				$includes['js'],
				$get_files(MODULES."/$module_name/includes/js", $absolute ? true : "components/modules/$module_name/includes/js")
			);
			$includes['html']		= array_merge(
				$includes['html'],
				$get_files(MODULES."/$module_name/includes/html", $absolute ? true : "components/modules/$module_name/includes/html")
			);
		}
		unset($module_name, $module_data);
		foreach ($Config->components['plugins'] as $plugin_name) {
			$includes['css']	= array_merge(
				$includes['css'],
				$get_files(PLUGINS."/$plugin_name/includes/css", $absolute ? true : "components/plugins/$plugin_name/includes/css")
			);
			$includes['js']		= array_merge(
				$includes['js'],
				$get_files(PLUGINS."/$plugin_name/includes/js", $absolute ? true : "components/plugins/$plugin_name/includes/js")
			);
			$includes['html']		= array_merge(
				$includes['html'],
				$get_files(PLUGINS."/$plugin_name/includes/html", $absolute ? true : "components/plugins/$plugin_name/includes/html")
			);
		}
		unset($plugin_name);
		return $includes;
	}
	/**
	 * Rebuilding of JavaScript and CSS cache
	 *
	 * @return Page
	 */
	protected function rebuild_cache () {
		$this->includes_dependencies_and_map($dependencies, $includes_map);
		$structure	= [];
		foreach ($includes_map as $filename_prefix => $includes) {
			$filename_prefix				= str_replace('/', '+', $filename_prefix);
			$structure[$filename_prefix]	= $this->create_cached_includes_files($filename_prefix, $includes);
		}
		unset($includes_map, $filename_prefix, $includes);
		file_put_json(PCACHE."/$this->pcache_basename.json", [
			'dependencies'	=> $dependencies,
			'structure'		=> $structure
		]);
		unset($structure);
		Trigger::instance()->run('System/Page/rebuild_cache');
		return $this;
	}
	/**
	 * Get dependencies of components between each other (only that contains some styles and scripts) and mapping styles and scripts to URL paths
	 *
	 * @param array	$dependencies
	 * @param array	$includes_map
	 */
	protected function includes_dependencies_and_map (&$dependencies, &$includes_map) {
		/**
		 * Get all includes
		 */
		$all_includes			= $this->get_includes_list(true);
		$includes_map			= [];
		$dependencies			= [];
		$dependencies_aliases	= [];
		/**
		 * According to components's maps some files should be included only on specific pages.
		 * Here we read this rules, and remove from whole includes list such items, that should be included only on specific pages.
		 * Also collect dependencies.
		 */
		$Config			= Config::instance();
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == -1) {
				continue;
			}
			if (file_exists(MODULES."/$module_name/meta.json")) {
				$meta	= file_get_json_nocomments(MODULES."/$module_name/meta.json");
				if (isset($meta['require'])) {
					foreach ((array)$meta['require'] as $r) {
						preg_match('/([^=<>]+)/', $r, $r);
						$dependencies[$module_name][]	= $r[0];
					}
					unset($r);
				}
				if (isset($meta['optional'])) {
					foreach ((array)$meta['optional'] as $o) {
						$dependencies[$module_name][]	= $o;
					}
					unset($o);
				}
				if (isset($meta['provide'])) {
					foreach ((array)$meta['provide'] as $p) {
						$dependencies_aliases[$p]	= $module_name;
					}
					unset($p);
				}
				unset($meta);
			}
			if (!file_exists(MODULES."/$module_name/includes/map.json")) {
				continue;
			}
			foreach (file_get_json_nocomments(MODULES."/$module_name/includes/map.json") as $path	=> $files) {
				foreach ($files as $file) {
					$extension							= file_extension($file);
					$file								= MODULES."/$module_name/includes/$extension/$file";
					$includes_map[$path][$extension][]	= $file;
					$all_includes[$extension]			= array_diff(
						$all_includes[$extension],
						[$file]
					);
				}
			}
			unset($path, $files, $file);
		}
		unset($module_name, $module_data);
		foreach ($Config->components['plugins'] as $plugin_name) {
			if (file_exists(PLUGINS."/$plugin_name/meta.json")) {
				$meta	= file_get_json_nocomments(PLUGINS."/$plugin_name/meta.json");
				if (isset($meta['require'])) {
					foreach ((array)$meta['require'] as $r) {
						preg_match('/([^=<>]+)/', $r, $r);
						$dependencies[$plugin_name][]	= $r[0];
					}
					unset($r);
				}
				if (isset($meta['optional'])) {
					foreach ((array)$meta['optional'] as $o) {
						$dependencies[$plugin_name][]	= $o;
					}
					unset($o);
				}
				if (isset($meta['provide'])) {
					foreach ((array)$meta['provide'] as $p) {
						$dependencies_aliases[$p]	= $plugin_name;
					}
					unset($p);
				}
				unset($meta);
			}
			if (!file_exists(PLUGINS."/$plugin_name/includes/map.json")) {
				continue;
			}
			foreach (file_get_json_nocomments(PLUGINS."/$plugin_name/includes/map.json") as $path => $files) {
				foreach ($files as $file) {
					$extension							= file_extension($file);
					$file								= PLUGINS."/$plugin_name/includes/$extension/$file";
					$includes_map[$path][$extension][]	= $file;
					$all_includes[$extension]			= array_diff(
						$all_includes[$extension],
						[$file]
					);
				}
			}
			unset($path, $files, $file);
		}
		unset($plugin_name);
		/**
		 * For consistency
		 */
		$includes_map['']	= $all_includes;
		unset($all_includes);
		/**
		 * Components can depend on each other - we need to find all dependencies and replace aliases by real names of components
		 */
		foreach ($dependencies as $component_name => &$depends_on) {
			foreach ($depends_on as $index => &$dependency) {
				if ($dependency == 'System') {
					continue;
				}
				if (isset($dependencies_aliases[$dependency])) {
					$dependency	= $dependencies_aliases[$dependency];
				}
				/**
				 * If dependency have its own dependencies, that are nor present in current component - add them and mark, that it is necessary
				 * to iterate through array again
				 */
				if (
					isset($dependencies[$dependency]) &&
					$dependencies[$dependency] &&
					array_diff($dependencies[$dependency], $depends_on)
				) {
					foreach (array_diff($dependencies[$dependency], $depends_on) as $new_dependency) {
						$depends_on[]	= $new_dependency;
					}
					unset($new_dependency);
				}
			}
			if (empty($depends_on)) {
				unset($dependencies[$component_name]);
			} else {
				$depends_on = array_unique($depends_on);
			}
		}
		unset($dependencies_aliases, $component_name, $depends_on, $index, $dependency);
		/**
		 * Clean dependencies without files
		 */
		foreach ($dependencies as &$depends_on) {
			foreach ($depends_on as $index => &$dependency) {
				if (!isset($includes_map[$dependency])) {
					unset($depends_on[$index]);
				}
			}
		}
		unset($depends_on, $index, $dependency);
	}
	/**
	 * Creates cached version of given js and css files.<br>
	 * Resulting file name consists of <b>$filename_prefix</b> and <b>$this->pcache_basename</b>
	 *
	 * @param string	$filename_prefix
	 * @param array		$includes			Array of paths to files, may have keys: <b>css</b> and/or <b>js</b> and/or <b>html</b>
	 *
	 * @return array
	 */
	protected function create_cached_includes_files ($filename_prefix, $includes) {
		$cache_hash	= [];
		foreach ($includes as $extension => &$files) {
			$files_content = '';
			foreach ($files as $file) {
				if (!file_exists($file)) {
					continue;
				}
				/**
				 * Insert external elements into resulting css file.
				 * It is needed, because those files will not be copied into new destination of resulting css file.
				 */
				if ($extension == 'css') {
					$files_content .= Includes_processing::css(
						file_get_contents($file),
						$file
					);
				/**
				 * Combine css and js files for Web Component into resulting files in order to optimize loading process
				 */
				} elseif ($extension == 'html') {
					$files_content .= Includes_processing::html(
						file_get_contents($file),
						$file,
						"$filename_prefix$this->pcache_basename-".basename($file).'+'.substr(md5($file), 0, 5),
						PCACHE
					);
				} else {
					$files_content .= file_get_contents($file).";\n";
				}
			}
			if ($filename_prefix == '' && $extension == 'js') {
				$files_content	= "window.cs.Language="._json_encode(Language::instance()).";$files_content";
			}
			file_put_contents(PCACHE."/$filename_prefix$this->pcache_basename.$extension", gzencode($files_content, 9), LOCK_EX | FILE_BINARY);
			$cache_hash[$extension]	= substr(md5($files_content), 0, 5);
		}
		return $cache_hash;
	}
	/**
	 * Getting footer information
	 *
	 * @return Page
	 */
	protected function get_footer () {
		$db				= class_exists('cs\\DB', false) ? DB::instance() : null;
		$this->Footer	.= h::div(
			get_core_ml_text('footer_text') ?: false,
			Config::instance()->core['show_footer_info'] ? Language::instance()->page_footer_info(
				'<!--generate time-->',
				$db ? $db->queries : 0,
				format_time(round($db ? $db->time : 0, 5)),
				'<!--peak memory usage-->'
			) : false,
			base64_decode(
				'wqkgUG93ZXJlZCBieSA8YSB0YXJnZXQ9Il9ibGFuayIgaHJlZj0iaHR0cDovL2NsZXZlcnN0eW'.
				'xlLm9yZy9jbXMiIHRpdGxlPSJDbGV2ZXJTdHlsZSBDTVMiPkNsZXZlclN0eWxlIENNUzwvYT4='
			)
		);
		return $this;
	}
	/**
	 * Getting of debug information
	 *
	 * @return Page
	 */
	protected function get_debug_info () {
		$Config				= Config::instance();
		$db					= DB::instance();
		$L					= Language::instance();
		$debug_tabs			= [];
		$debug_tabs_content	= '';
		/**
		 * DB queries
		 */
		if ($Config->core['show_db_queries']) {
			$debug_tabs[]		= $L->db_queries;
			$tmp				= '';
			foreach ($db->get_connections_list() as $name => $database) {
				$queries	= $database->queries();
				$tmp		.= h::{'p.cs-padding-left'}(
					$L->debug_db_info(
						$name != 0 ? $L->db.' '.$database->database() : $L->core_db.' ('.$database->database().')',
						format_time(round($database->connecting_time(), 5)),
						$queries['num'],
						format_time(round($database->time(), 5))
					)
				);
				foreach ($queries['text'] as $i => $text) {
					$tmp	.= h::code(
						$text.
						h::br(2).
						'#'.h::i(format_time(round($queries['time'][$i], 5))),
						[
							'class' => ($queries['time'][$i] > .1 ? 'uk-alert-danger ' : '').'uk-alert'
						]
					);
				}
			}
			unset($error, $name, $database, $i, $text);
			$debug_tabs_content	.= h::div(
				h::p(
					$L->debug_db_total($db->queries, format_time(round($db->time, 5))),
					$L->failed_connections.': '.h::b(implode(', ', $db->get_connections_list(false)) ?: $L->no),
					$L->successful_connections.': '.h::b(implode(', ', $db->get_connections_list(true)) ?: $L->no),
					$L->mirrors_connections.': '.h::b(implode(', ', $db->get_connections_list('mirror')) ?: $L->no),
					$L->active_connections.': '.(count($db->get_connections_list()) ? '' : h::b($L->no))
				).
				$tmp
			);
			unset($tmp);
		}
		/**
		 * Cookies
		 */
		if ($Config->core['show_cookies']) {
			$debug_tabs[]		= $L->cookies;
			$tmp				= [h::td($L->key.':', ['style' => 'width: 20%;']).h::td($L->value)];
			foreach ($_COOKIE as $i => $v) {
				$tmp[]	= h::td($i.':', ['style' => 'width: 20%;']).h::td(xap($v));
			}
			unset($i, $v);
			$debug_tabs_content	.= h::{'table.cs-padding-left tr'}($tmp);
			unset($tmp);
		}
		$this->debug_info = $this->process_replacing(
			h::{'ul.cs-tabs li'}($debug_tabs).
			h::div($debug_tabs_content)
		);
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
		$this->Top .= h::{'div.uk-alert.uk-alert-success.uk-lead.cs-center'}(
			$success_text
		);
		return $this;
	}
	/**
	 * Display notice message
	 *
	 * @param string $notice_text
	 *
	 * @return Page
	 */
	function notice ($notice_text) {
		$this->Top .= h::{'div.uk-alert.uk-alert-warning.uk-lead.cs-center'}(
			$notice_text
		);
		return $this;
	}
	/**
	 * Display warning message
	 *
	 * @param string $warning_text
	 *
	 * @return Page
	 */
	function warning ($warning_text) {
		$this->Top .= h::{'div.uk-alert.uk-alert-danger.cs-center'}(
			$warning_text
		);
		return $this;
	}
	/**
	 * Error pages processing
	 *
	 * @param null|string|string[]	$custom_text	Custom error text instead of text like "404 Not Found",
	 * 												or array with two elements: [error, error_description]
	 * @param bool					$json			Force JSON return format
	 */
	function error ($custom_text = null, $json = false) {
		static $error_showed = false;
		if ($error_showed) {
			return;
		}
		$error_showed	= true;
		if (!error_code()) {
			error_code(500);
		}
		if (!api_path() && error_code() == 403 && _getcookie('sign_out')) {
			header('Location: '.Config::instance()->base_url(), true, 302);
			$this->Content	= '';
			exit;
		}
		interface_off();
		$error	= code_header(error_code());
		if (is_array($custom_text)) {
			$error				= $custom_text[0];
			$error_description	= $custom_text[1];
		} else {
			$error_description	= $custom_text ? : $error;
		}
		if (api_path() || $json) {
			if ($json) {
				header('Content-Type: application/json; charset=utf-8', true);
				interface_off();
			}
			$this->json([
				'error'				=> $error,
				'error_description'	=> $error_description
			]);
		} else {
			ob_start();
			if (
				!_include_once(THEMES."/$this->theme/error.html", false) &&
				!_include_once(THEMES."/$this->theme/error.php", false)
			) {
				echo "<!doctype html>\n".
					h::title(code_header($error)).
					 ($error_description ?: $error);
			}
			$this->Content	= ob_get_clean();
		}
		$this->__finish();
		exit;
	}
	/**
	 * Substitutes header information about user, sign in/sign up forms, etc.
	 *
	 * @return Page
	 */
	protected function get_header_info () {
		$L							= Language::instance();
		$User						= User::instance(true);
		$this->user_avatar_image	= $User->avatar();
		if ($User->user()) {
			$this->header_info = h::{'div.cs-header-user-block'}(
				h::b(
					"$L->hello, ".$User->username().'! '.
					h::{'icon.cs-header-sign-out-process'}(
						'sign-out',
						[
							'style'			=> 'cursor: pointer;',
							'data-title'	=> $L->sign_out
						]
					)
				).
				h::div(
					h::a(
						$L->profile,
						[
							'href'	=> path($L->profile)."/$User->login"
						]
					).
					' | '.
					h::a(
						$L->settings,
						[
							'href'	=> path($L->profile).'/'.path($L->settings)
						]
					)
				).
				$this->header_info
			);
			Trigger::instance()->run('System/Page/get_header_info');
		} else {
			$external_systems_list		= '';
			Trigger::instance()->run(
				'System/Page/external_sign_in_list',
				[
					'list'	=> &$external_systems_list
				]
			);
			$this->header_info			= h::{'div.cs-header-guest-form'}(
				h::b("$L->hello, $L->guest!").
				h::div(
					h::{'button.cs-header-sign-in-slide.cs-button-compact.uk-icon-sign-in'}($L->sign_in).
					h::{'button.cs-header-registration-slide.cs-button-compact.uk-icon-pencil'}(
						$L->sign_up,
						[
							'data-title'	=> $L->quick_registration_form
						]
					)
				)
			).
			h::{'div.cs-header-restore-password-form'}(
				h::{'input.cs-no-ui.cs-header-restore-password-email[tabindex=1]'}([
					'placeholder'		=> $L->login_or_email,
					'autocapitalize'	=> 'off',
					'autocorrect'		=> 'off'
				]).
				h::br().
				h::{'button.cs-header-restore-password-process.cs-button-compact.uk-icon-question[tabindex=2]'}($L->restore_password).
				h::{'button.cs-button-compact.cs-header-back[tabindex=3]'}(
					h::icon('chevron-down'),
					[
						'data-title'	=> $L->back
					]
				),
				[
					'style'	=> 'display: none;'
				]
			).
			h::{'div.cs-header-registration-form'}(
				h::{'input.cs-no-ui.cs-header-registration-email[type=email][tabindex=1]'}([
					'placeholder'		=> $L->email,
					'autocapitalize'	=> 'off',
					'autocorrect'		=> 'off'
				]).
				h::br().
				h::{'button.cs-header-registration-process.cs-button-compact.uk-icon-pencil[tabindex=2]'}($L->sign_up).
				h::{'button.cs-button-compact.cs-header-back[tabindex=4]'}(
					h::icon('chevron-down'),
					[
						'data-title'	=> $L->back
					]
				),
				[
					'style'	=> 'display: none;'
				]
			).
			h::{'form.cs-header-sign-in-form.cs-no-ui'}(
				h::{'input.cs-no-ui.cs-header-sign-in-email[tabindex=1]'}([
					'placeholder'		=> $L->login_or_email,
					'autocapitalize'	=> 'off',
					'autocorrect'		=> 'off'
				]).
				h::{'input.cs-no-ui.cs-header-user-password[type=password][tabindex=2]'}([
					'placeholder'	=> $L->password
				]).
				h::br().
				h::{'button.cs-button-compact.uk-icon-sign-in[tabindex=3][type=submit]'}($L->sign_in).
				h::{'button.cs-button-compact.cs-header-back[tabindex=5]'}(
					h::icon('chevron-down'),
					[
						'data-title'	=> $L->back
					]
				).
				h::{'button.cs-button-compact.cs-header-restore-password-slide[tabindex=4]'}(
					h::icon('question'),
					[
						'data-title'	=> $L->restore_password
					]
				),
				[
					'style'	=> 'display: none;'
				]
			).
			$external_systems_list;
		}
		return $this;
	}
	/**
	 * Page generation
	 */
	function __finish () {
		static $executed = false;
		if ($executed) {
			return;
		}
		$executed	= true;
		/**
		 * Cleaning of output
		 */
		if (OUT_CLEAN) {
			ob_end_clean();
		}
		/**
		 * Detection of compression
		 */
		$ob					= false;
		$Config				= Config::instance(true);
		if (
			api_path() ||
			(
				$Config &&
				!zlib_compression() &&
				$Config->core['gzip_compression']
			)
		) {
			ob_start('ob_gzhandler');
			$ob = true;
		}
		/**
		 * For AJAX and API requests only content without page template
		 */
		if (!$this->interface) {
			/**
			 * Processing of replacing in content
			 */
			echo $this->process_replacing($this->Content ?: (api_path() ? 'null' : ''));
		} else {
			Trigger::instance()->run('System/Page/pre_display');
			/**
			 * Processing of template, substituting of content, preparing for the output
			 */
			$this->prepare();
			/**
			 * Processing of replacing in content
			 */
			$this->Html			= $this->process_replacing($this->Html);
			/**
			 * Getting of debug information
			 */
			if (
				DEBUG &&
				(
					User::instance(true)->admin() ||
					(
						$Config->can_be_admin &&
						$Config->core['ip_admin_list_only']
					)
				)
			) {
				$this->get_debug_info();
			}
			Trigger::instance()->run('System/Page/display');
			echo str_replace(
				[
					'<!--debug_info-->',
					'<!--generate time-->',
					'<!--peak memory usage-->'
				],
				[
					$this->debug_info ? h::level(
						h::{'div#cs-debug.uk-modal div.uk-modal-dialog-large'}(
							h::level($this->debug_info),
							[
								'title'			=> Language::instance()->debug,
								'style'			=> 'margin-left: -45%; width: 90%;'
							]
						),
						$this->level['debug_info']
					) : '',
					format_time(round(microtime(true) - MICROTIME, 5)),
					format_filesize(memory_get_usage(), 5).h::{'sup[level=0]'}(format_filesize(memory_get_peak_usage(), 5))
				],
				rtrim($this->Html)
			);
		}
		if ($ob) {
			ob_end_flush();
		}
	}
}
