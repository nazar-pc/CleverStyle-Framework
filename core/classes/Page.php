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
	cs\Page\Includes,
	cs\Page\Open_graph;

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
	use
		Singleton,
		Includes,
		Open_graph;
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
	public	$link			= [];
	public	$Search			= [];
	public	$Replace		= [];
	public	$canonical_url	= false;
	protected	$theme, $color_scheme;
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
		$Config	= Config::instance();
		if (admin_path()) {
			$this->theme		= 'CleverStyle';
			$this->color_scheme	= 'Default';
		} else {
			/**
			 * Theme detection
			 */
			if (is_object($Config)) {
				$this->theme		= in_array($this->theme, $Config->core['themes']) ? $this->theme : $Config->core['theme'];
				$this->color_scheme	= in_array($this->color_scheme, $Config->core['color_schemes'][$this->theme]) ?
										$this->color_scheme : $Config->core['color_schemes'][$this->theme][0];
			}
		}
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
		$Config	= Config::instance(true);
		/**
		 * Loading of template
		 * Getting user information
		 */
		$this->get_template()->get_header_info();
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
		 * Forming <head> content
		 */
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
		$this->Head			=
			h::title($this->Title).
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
			);
		/**
		 * Addition of CSS, JavaScript and Web Components includes
		 */
		$this->add_includes_on_page();
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
