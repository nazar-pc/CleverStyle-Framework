<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
/**
 * Provides next triggers:<br>
 *  System/Page/pre_display
 *  System/Page/get_header_info
 *  System/Page/rebuild_cache
 *  ['key'	=> <i>&$key</i>]		//Reference to the key, that will be appended to all css and js files, can be changed to reflect JavaScript and CSS changes<br>
 *  System/Page/external_login_list
 *  ['list'	=> <i>&$list</i>]		//Reference to the list of external login systems
 */
class Page {
	public		$Content,
				$interface		= true,
				$pre_Html		= '',
				$Html 			= '',
					$Keywords		= '',
					$Description	= '',
					$Title			= [],
				$debug_info		= '',
				$Head			= '',
				$pre_Body		= '',
					$Header		= '',
						$mainmenu		= '',
						$mainsubmenu	= '',
						$menumore		= '',
					$Left		= '',
					$Top		= '',
					$Right		= '',
					$Bottom		= '',
					$Footer		= '',
				$post_Body		= '',
				$post_Html		= '',
				$level			= [				//Number of tabs by default for margins the substitution
					'Head'				=> 0,	//of values into template
					'pre_Body'			=> 0,
					'Header'			=> 2,
					'mainmenu'			=> 2,
					'mainsubmenu'		=> 2,
					'menumore'			=> 2,
					'header_info'		=> 3,
					'debug_info'		=> 1,
					'Left'				=> 2,
					'Top'				=> 2,
					'Content'			=> 3,
					'Bottom'			=> 2,
					'Right'				=> 2,
					'Footer'			=> 1,
					'post_Body'			=> 0
				],
				$user_avatar_image,
				$header_info,
				$head_prefix	= '',			//Is used as <head prefix="$head_prefix">
				$no_head		= false;
	protected	$theme, $color_scheme, $pcache_basename, $includes,
				$core_js		= [0 => [], 1 => []],
				$core_css		= [0 => [], 1 => []],
				$js				= [0 => [], 1 => []],
				$css			= [0 => [], 1 => []],
				$link			= [],
				$Search			= [],
				$Replace		= [],
				$og_data		= [],
				$og_type		= '',
				$canonical_url	= false;
	/**
	 * Setting interface state on/off
	 */
	function __construct () {
		global $interface;
		$this->interface = (bool)$interface;
		unset($GLOBALS['interface']);
	}
	/**
	 * Initialization: setting of title, keywords, description, theme and color scheme according to specified parameters
	 *
	 * @param string	$title
	 * @param string	$keywords
	 * @param string	$description
	 * @param string	$theme
	 * @param string	$color_scheme
	 *
	 * @return Page
	 */
	function init ($title, $keywords, $description, $theme, $color_scheme) {
		$this->Title[0] = htmlentities($title, ENT_COMPAT, 'utf-8');
		$this->Keywords = $keywords;
		$this->Description = $description;
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
	 * Colos scheme changing
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
		if (!API) {
			header('Content-Type: application/json', true);
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
		global $Config, $L, $User;
		/**
		 * Theme detection
		 */
		if (is_object($Config)) {
			$this->theme		= in_array($this->theme, $Config->core['active_themes']) ? $this->theme : $Config->core['theme'];
			if ($Config->core['allow_change_theme']) {
				$theme				= _getcookie('theme');
				if ($theme && $theme !== $this->theme && in_array($theme, $Config->core['active_themes'])) {
					$this->theme = $theme;
				}
				unset($theme);
			}
			$this->color_scheme	= in_array($this->color_scheme, $Config->core['color_schemes'][$this->theme]) ?
									$this->color_scheme : $Config->core['color_schemes'][$this->theme][0];
			if ($Config->core['allow_change_theme']) {
				$color_scheme		= _getcookie('color_scheme');
				if ($color_scheme && $color_scheme !== $this->color_scheme && in_array($color_scheme, $Config->core['color_schemes'][$this->theme])) {
					$this->color_scheme = $color_scheme;
				}
				unset($color_scheme);
			}
		}
		/**
		 * Base name for cache files
		 */
		$this->pcache_basename = '_'.$this->theme.'_'.$this->color_scheme.'_'.$L->clang.'.';
		/**
		 * Template loading
		 */
		if ($this->interface) {
			_include_once(THEMES.'/'.$this->theme.'/prepare.php', false);
			ob_start();
			if (
				!(
					file_exists(THEMES.'/'.$this->theme.'/index.html') || file_exists(THEMES.'/'.$this->theme.'/index.php')
				) ||
				(
					!(
						is_object($Config) && $Config->core['site_mode']
					) &&
					!(
						is_object($User) && $User->admin()
					) &&
					code_header(503) &&
					!(
						_include_once(THEMES.'/'.$this->theme.'/closed.php', false) || _include_once(THEMES.'/'.$this->theme.'/closed.html', false)
					)
				)
			) {
				echo	"<!doctype html>\n".
				h::title(get_core_ml_text('closed_title')).
				get_core_ml_text('closed_text');
			} else {
				_include_once(THEMES.'/'.$this->theme.'/index.php', false) || _include_once(THEMES.'/'.$this->theme.'/index.html');
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
		global $Config;
		/**
		 * Loading of template
		 * Loading of CSS and JavaScript
		 * Getting user information
		 */
		$this->get_template()->get_includes()->get_header_info();
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
		if (is_object($Config)) {
			$this->Title = $Config->core['title_reverse'] ? array_reverse($this->Title) : $this->Title;
			$this->Title = implode($Config->core['title_delimiter'], $this->Title);
		} else {
			$this->Title = $this->Title[0];
		}
		/**
		 * Forming <head> content
		 */
		$this->core_css[0]	= implode('', array_unique($this->core_css[0]));
		$this->core_css[1]	= implode('', array_unique($this->core_css[1]));
		$this->css[0]		= implode('', array_unique($this->css[0]));
		$this->css[1]		= implode('', array_unique($this->css[1]));
		$this->core_js[0]	= implode('', array_unique($this->core_js[0]));
		$this->core_js[1]	= implode('', array_unique($this->core_js[1]));
		$this->js[0]		= implode('', array_unique($this->js[0]));
		$this->js[1]		= implode('', array_unique($this->js[1]));
		if ($this->core_css[1]) {
			$this->core_css[1]	= h::style($this->core_css[1]);
		}
		if ($this->css[1]) {
			$this->css[1]		= h::style($this->css[1]);
		}
		if ($this->core_js[1]) {
			$this->core_js[1]	= h::script($this->core_js[1]);
		}
		if ($this->js[1]) {
			$this->js[1]		= h::script($this->js[1]);
		}
		if (file_exists(THEMES.'/'.$this->theme.'/'.$this->color_scheme.'/'.'img/favicon.png')) {
			$favicon	= 'themes/'.$this->theme.'/'.$this->color_scheme.'/img/favicon.png';
		} elseif (file_exists(THEMES.'/'.$this->theme.'/'.$this->color_scheme.'/img/favicon.ico')) {
			$favicon	= 'themes/'.$this->theme.'/'.$this->color_scheme.'/img/favicon.ico';
		} elseif (file_exists(THEMES.'/'.$this->theme.'/img/favicon.png')) {
			$favicon	= 'themes/'.$this->theme.'/img/favicon.png';
		} elseif (file_exists(THEMES.'/'.$this->theme.'/img/favicon.ico')) {
			$favicon	= 'themes/'.$this->theme.'/img/favicon.ico';
		} else {
			$favicon	= 'includes/img/favicon.ico';
		}
		$this->Head			=	h::title($this->Title).
			h::meta(
				[
					'charset'		=> 'utf-8'
				],
				[
					'name'			=> 'keywords',
					'content'		=> $this->Keywords
				],
				[
					'name'			=> 'description',
					'content'		=> $this->Description
				],
				[
					'name'			=> 'generator',
					'content'		=> base64_decode('Q2xldmVyU3R5bGUgQ01TIGJ5IE1va3J5bnNreWkgTmF6YXI=')
				],
				ADMIN || API ? [
					'name'			=> 'robots',
					'content'		=> 'noindex,nofollow'
				] : false
			).
			h::base(is_object($Config) ? [
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
			implode('', $this->core_css).
			implode('', $this->css).
			implode('', $this->core_js).
			implode('', $this->js);
		/**
		 * Generation of Open Graph protocol information
		 */
		$this->og_generation();
		/**
		 * Getting footer information
		 */
		$this->get_footer();
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
				'<!--main-submenu-->',
				'<!--menu-more-->',
				'<!--user_avatar_image-->',
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
				h::level($this->mainmenu, $this->level['mainmenu']),
				h::level($this->mainsubmenu, $this->level['mainsubmenu']),
				h::level($this->menumore, $this->level['menumore']),
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
	 * Replacing anything in source code of finally genereted page
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
		errors_on();
		return $data;
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
	 * @param string	$add
	 * @param string	$mode
	 * @param bool		$core
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
	 * @param string	$add
	 * @param string	$mode
	 * @param bool		$core
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
	 * @param array|bool	$data	According to h class syntax
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
		global $Config;
		$this->canonical_url	= HOME ? $Config->base_url() : $url;
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
		global $Config;
		if (!$Config->core['og_support']) {
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
		global $Config;
		if (!$Config->core['og_support']) {
			return;
		}
		$og	= &$this->og_data;
		if (!isset($og['title']) || empty($og['title'])) {
			$this->og('title', $this->Title);
		}
		if (!isset($og['description']) || empty($og['description'])) {
			$this->og('description', $this->Description);
		}
		if (!isset($og['url']) || empty($og['url'])) {
			$this->og('url', HOME ? $Config->base_url() : ($this->canonical_url ?: $Config->base_url().'/'.$Config->server['relative_address']));
		}
		if (!isset($og['site_name']) || empty($og['site_name'])) {
			$this->og('site_name', get_core_ml_text('name'));
		}
		if (!isset($og['type']) || empty($og['type'])) {
			$this->og('type', 'website');
		}
		if ($Config->core['multilingual']) {
			global $L;
			if (!isset($og['locale']) || empty($og['locale'])) {
				$this->og('locale', $L->locale);
			}
			if (
				(
					!isset($og['locale:alternate']) || empty($og['locale:alternate'])
				) && count($Config->core['active_languages']) > 1
			) {
				foreach ($Config->core['active_languages'] as $lang) {
					if ($lang != $Config->core['language']) {
						$this->og('locale:alternate', $L->get('locale', $lang));
					}
				}
			}
		}
		$prefix	= 'og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#';
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
	protected function get_includes () {
		global $Config;
		if (!is_object($Config)) {
			return $this;
		}
		/**
		 * If CSS and JavaScript compression enabled
		 */
		if ($Config->core['cache_compress_js_css']) {
			/**
			 * Current cache checking
			 */
			if (
				!file_exists(PCACHE.'/'.$this->pcache_basename.'css') ||
				!file_exists(PCACHE.'/'.$this->pcache_basename.'js') ||
				!file_exists(PCACHE.'/pcache_key')
			) {
				$this->rebuild_cache();
			}
			$key = file_get_contents(PCACHE.'/pcache_key');
			/**
			 * Including of CSS
			 */
			$css_list = get_files_list(PCACHE, '/^[^_](.*)\.css$/i', 'f', 'storage/pcache');
			$css_list = array_merge(
				['storage/pcache/'.$this->pcache_basename.'css'],
				$css_list
			);
			foreach ($css_list as &$file) {
				$file .= '?'.$key;
			}
			unset($file);
			$this->css_internal($css_list, 'file', true);
			/**
			 * Including of JavaScript
			 */
			$js_list = get_files_list(PCACHE, '/^[^_](.*)\.js$/i', 'f', 'storage/pcache');
			$js_list = array_merge(
				['storage/pcache/'.$this->pcache_basename.'js'],
				$js_list
			);
			foreach ($js_list as &$file) {
				$file .= '?'.$key;
			}
			unset($file);
			$this->js_internal($js_list, 'file', true);
		} else {
			$this->get_includes_list();
			/**
			 * Including of CSS
			 */
			foreach ($this->includes['css'] as $file) {
				$this->css_internal($file, 'file', true);
			}
			/**
			 * Including of JavaScript
			 */
			foreach ($this->includes['js'] as $file) {
				$this->js_internal($file, 'file', true);
			}
		}
		return $this;
	}
	/**
	 * Getting of JavaScript and CSS files list to be included
	 *
	 * @param bool	$for_cache
	 *
	 * @return Page
	 */
	protected function get_includes_list ($for_cache = false) {
		$theme_folder	= THEMES.'/'.$this->theme;
		$scheme_folder	= $theme_folder.'/schemes/'.$this->color_scheme;
		$theme_pfolder	= 'themes/'.$this->theme;
		$scheme_pfolder	= $theme_pfolder.'/schemes/'.$this->color_scheme;
		$this->includes = [
			'css' => array_merge(
				(array)get_files_list(CSS,						'/(.*)\.css$/i',	'f', $for_cache ? true : 'includes/css',			true, false, '!include'),
				(array)get_files_list($theme_folder.'/css',		'/(.*)\.css$/i',	'f', $for_cache ? true : $theme_pfolder.'/css',		true, false, '!include'),
				(array)get_files_list($scheme_folder.'/css',	'/(.*)\.css$/i',	'f', $for_cache ? true : $scheme_pfolder.'/css',	true, false, '!include')
			),
			'js' => array_merge(
				(array)get_files_list(JS,						'/(.*)\.js$/i',		'f', $for_cache ? true : 'includes/js',				true, false, '!include'),
				(array)get_files_list($theme_folder.'/js',		'/(.*)\.js$/i',		'f', $for_cache ? true : $theme_pfolder.'/js',		true, false, '!include'),
				(array)get_files_list($scheme_folder.'/js',		'/(.*)\.js$/i',		'f', $for_cache ? true : $scheme_pfolder.'/js',		true, false, '!include')
			)
		];
		unset($theme_folder, $scheme_folder, $theme_pfolder, $scheme_pfolder);
		sort($this->includes['css']);
		sort($this->includes['js']);
		return $this;
	}
	/**
	 * Rebuilding of JavaScript and CSS cache
	 *
	 * @return Page
	 */
	protected function rebuild_cache () {
		global $Core, $L;
		$key	= '';
		$Core->run_trigger(
			'System/Page/rebuild_cache',
			[
				'key'	=> &$key
			]
		);
		$this->get_includes_list(true);
		foreach ($this->includes as $extension => &$files) {
			$temp_cache = '';
			foreach ($files as $file) {
				if (file_exists($file)) {
					$current_cache = file_get_contents($file);
					if ($extension == 'css') {
						/**
						 * Insert external elements into resulting css file.
						 * It is needed, because those files will not be copied into new destination of resulting css file.
						 */
						$this->css_includes_processing($current_cache, $file);
					}
					$temp_cache .= $current_cache;
					unset($current_cache);
				}
			}
			if ($extension == 'js') {
				$temp_cache	.= 'var L='.$L->get_json().';';
			}
			file_put_contents(PCACHE.'/'.$this->pcache_basename.$extension, gzencode($temp_cache, 9), LOCK_EX | FILE_BINARY);
			$key .= md5($temp_cache);
		}
		file_put_contents(PCACHE.'/pcache_key', mb_substr(md5($key), 0, 5), LOCK_EX | FILE_BINARY);
		return $this;
	}
	/**
	 * Analyses file for images, fonts and css links and include they content into single resulting css file.<br>
	 * Supports next file extensions for possible includes:<br>
	 * jpeg, jpe, jpg, gif, png, ttf, ttc, svg, svgz, woff, eot, css
	 *
	 * @param string	$data	Content of processed file
	 * @param string	$file	Path to file, that includes specified in previous parameter content
	 *
	 * @return	string			$data
	 */
	function css_includes_processing (&$data, $file) {
		$cwd	= getcwd();
		chdir(dirname($file));
		/**
		 * Simple minification, removes comments, newlines, tabs and unnecessary spaces
		 */
		$data	= preg_replace('#(/\*.*?\*/)|\t|\n|\r#s', '', $data);
		$data	= preg_replace('#\s*([,:;+>{}])\s*#s', '$1', $data);
		$data	= str_replace(';}', '}', $data);
		/**
		 * Includes processing
		 */
		$data	= preg_replace_callback(
			'/(url\((.*?)\))|(@import[\s\t\n\r]{0,1}[\'"](.*?)[\'"])/',
			function ($match) use (&$data) {
				$link		= trim($match[count($match) - 1], '\'" ');
				if (
					mb_strpos($link, 'http://') === 0 ||
					mb_strpos($link, 'https://') === 0 ||
					mb_strpos($link, 'ftp://') === 0 ||
					mb_strpos($link, '/') === 0 ||
					!file_exists(realpath($link))
				) {
					return $match[0];
				}
				$format		= mb_substr($link, mb_strrpos($link, '.') + 1);
				$mime_type	= 'text/html';
				switch ($format) {
					case 'jpeg':
					case 'jpe':
					case 'jpg':
						$mime_type = 'image/jpg';
					break;
					case 'gif':
						$mime_type = 'image/gif';
					break;
					case 'png':
						$mime_type = 'image/png';
					break;
					case 'ttf':
					case 'ttc':
						$mime_type = 'application/x-font-ttf';
					break;
					case 'svg':
					case 'svgz':
						$mime_type = 'image/svg+xml';
					break;
					case 'woff':
						$mime_type = 'application/x-font-woff';
					break;
					case 'eot':
						$mime_type = 'application/vnd.ms-fontobject';
					break;
					case 'css':
						$mime_type = 'text/css';
					break;
				}
				$content	= file_get_contents(realpath($link));
				/**
				 * For recursive includes processing, if CSS file includes others CSS files
				 */
				if ($format == 'css') {
					$this->css_includes_processing($content, realpath($link));
				}
				return str_replace($match[count($match) - 1], 'data:'.$mime_type.';charset=utf-8;base64,'.base64_encode($content), $match[0]);
			},
			$data
		);
		chdir($cwd);
		return $data;
	}
	/**
	 * Getting footer information
	 *
	 * @return Page
	 */
	protected function get_footer () {
		global $L, $db, $Config;
		$this->Footer .= h::div(
			get_core_ml_text('footer_text') ?: false,
			$Config->core['show_footer_info'] ?
				$L->page_footer_info('<!--generate time-->', $db->queries, format_time(round($db->time, 5)), '<!--peak memory usage-->') : false,
			base64_decode(
				'wqkgUG93ZXJlZCBieSA8YSB0YXJnZXQ9Il9ibGFuayIgaHJlZj0iaHR0cDovL2NsZXZlcnN0eW'
				.'xlLm9yZy9jbXMiIHRpdGxlPSJDbGV2ZXJTdHlsZSBDTVMiPkNsZXZlclN0eWxlIENNUzwvYT4='
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
		global $Config, $L, $db;
		$debug_tabs			= '';
		$debug_tabs_content	= '';
		/**
		 * Objects
		 */
		if ($Config->core['show_objects_data']) {
			global $Core, $timeload, $loader_init_memory;
			$debug_tabs[]			= [
				$L->objects,
				[
					'href'	=> '#debug_objects_tab'
				]
			];
			$tmp				= '';
			$last				= $timeload['loader_init'];
			foreach ($Core->Loaded as $object => $data) {
				$tmp .=	h::p(
					$object
				).
				h::{'p.cs-padding-left'}(
					$L->creation_duration.': '.format_time(round($data[0] - $last, 5)),
					$L->time_from_start_execution.': '.format_time(round($data[0] - $timeload['start'], 5)),
					$L->memory_usage.': '.format_filesize($data[1], 5)
				);
				$last = $data[0];
			}
			unset($object, $data, $last);
			$debug_tabs_content	.= h::{'div#debug_objects_tab'}(
				h::p(
					$L->total_list_of_objects.': '.implode(', ', array_keys($Core->Loaded)),
					$L->loader
				).
				h::{'p.cs-padding-left'}(
					$L->creation_duration.': '.format_time(round($timeload['loader_init'] - $timeload['start'], 5)),
					$L->memory_usage.': '.format_filesize($loader_init_memory, 5)
				).
				$tmp
			);
			unset($tmp);
		}
		/**
		 * DB queries
		 */
		if ($Config->core['show_db_queries']) {
			$debug_tabs[]		= [
				$L->db_queries,
				[
					'href'	=> '#debug_db_queries_tab'
				]
			];
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
							'class' => ($queries['time'][$i] > .1 ? 'ui-state-highlight ' : '').'cs-debug-code'
						]
					);
				}
			}
			unset($error, $name, $database, $i, $text);
			$debug_tabs_content	.= h::{'div#debug_db_queries_tab'}(
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
			$debug_tabs[]		= [
				$L->cookies,
				[
					'href'	=> '#debug_cookies_tab'
				]
			];
			$tmp				= [h::td($L->key.':', ['style' => 'width: 20%;']).h::td($L->value)];
			foreach ($_COOKIE as $i => $v) {
				$tmp[]	= h::td($i.':', ['style' => 'width: 20%;']).h::td(xap($v));
			}
			unset($i, $v);
			$debug_tabs_content	.= h::{'div#debug_cookies_tab'}(
				h::{'table.cs-padding-left'}(
					h::tr($tmp),
					[
						'style' => 'width: 100%'
					]
				)
			);
			unset($tmp);
		}
		$this->debug_info = $this->process_replacing(
			h::{'div#debug_window_tabs'}(
				h::{'ul li| a'}($debug_tabs).
				$debug_tabs_content
			)
		);
		return $this;
	}
	/**
	 * Display notice
	 *
	 * @param string $notice_text
	 *
	 * @return Page
	 */
	function notice ($notice_text) {
		$this->Top .= h::{'div.ui-state-highlight.ui-corner-all.ui-priority-primary.cs-center.cs-state-messages'}(
			$notice_text
		);
		return $this;
	}
	/**
	 * Display warning
	 *
	 * @param string $warning_text
	 *
	 * @return Page
	 */
	function warning ($warning_text) {
		$this->Top .= h::{'div.ui-state-error.ui-corner-all.ui-priority-primary.cs-center.cs-state-messages'}(
			$warning_text
		);
		return $this;
	}
	/**
	 * Error pages processing
	 *
	 * @param null|string	$custom_text	Custom error text instead of text like "404 Not Found"
	 * @param bool			$json			Force JSON return format
	 */
	function error ($custom_text = null, $json = false) {
		static $error_showed = false;
		if ($error_showed) {
			return;
		}
		$error_showed	= true;
		if (!defined('ERROR_CODE')) {
			define('ERROR_CODE', 500);
		}
		if (!API && ERROR_CODE == 403 && _getcookie('logout')) {
			global $Config;
			header('Location: '.$Config->base_url(), true, 302);
			$this->Content	= '';
			__finish();
		}
		interface_off();
		$error_text	= code_header(ERROR_CODE);
		$error_text	= $custom_text ?: $error_text;
		if (API || $json) {
			if ($json) {
				header('Content-Type: application/json', true);
				interface_off();
			}
			$this->json([
				'error'				=> ERROR_CODE,
				'error_description'	=> $error_text
			]);
		} else {
			ob_start();
			if (
				!_include_once(THEMES.'/'.$this->theme.'/error.html', false) &&
				!_include_once(THEMES.'/'.$this->theme.'/error.php', false)
			) {
				echo "<!doctype html>\n".
					h::title($error_text ?: ERROR_CODE).
					 ($error_text ?: ERROR_CODE);
			}
			$this->Content	= ob_get_clean();
			__finish();
		}
	}
	/**
	 * Substitutes header information about user, login/registration forms, etc.
	 *
	 * @return Page
	 */
	protected function get_header_info () {
		global $User, $L, $Core;
		if (is_object ($User) && $User->user()) {
			if ($User->avatar) {
				$this->user_avatar_image = 'url('.h::url($User->avatar, true).')';
			} else {
				$this->user_avatar_image = 'url(/includes/img/guest.gif)';
			}
			$this->header_info = h::b($L->hello.', '.$User->username().'!').
			h::{'icon.cs-header-logout-process'}(
				'power',
				[
					'style'			=> 'cursor: pointer;',
					'data-title'	=> $L->log_out
				]
			).
			h::{'p.actions'}(
				h::a(
					$L->profile,
					[
						'href'	=> path($L->profile).'/'.$User->login
					]
				).
				'|'.
				h::a(
					$L->settings,
					[
						'href'	=> path($L->profile).'/'.path($L->settings)
					]
				)
			).
			$this->header_info;
			$Core->run_trigger('System/Page/get_header_info');
		} else {
			$external_systems_list		= '';
			$Core->run_trigger(
				'System/Page/external_login_list',
				[
					'list'	=> &$external_systems_list
				]
			);
			$this->user_avatar_image	= 'url(/includes/img/guest.gif)';
			$this->header_info			= h::{'div.cs-header-anonym-form'}(
				h::b($L->hello.', '.$L->guest.'!').
				h::br().
				h::{'button.cs-header-login-slide.cs-button-compact'}(
					h::icon('check').$L->log_in
				).
				h::{'button.cs-header-registration-slide.cs-button-compact'}(
					h::icon('pencil').$L->registration,
					[
						 'data-title'	=> $L->quick_registration_form
					]
				)
			).
			h::{'div.cs-header-restore-password-form'}(
				h::{'input.cs-noui.cs-header-restore-password-email[tabindex=1]'}(
					[
						'placeholder'	=> $L->login_or_email
					]
				).
				h::{'button.cs-header-restore-password-process.cs-button-compact[tabindex=2]'}(
					h::icon('help').$L->restore_password
				).
				h::div().
				h::{'button.cs-button-compact.cs-header-back[tabindex=3]'}(
					h::icon('carat-1-s'),
					[
						'data-title'	=> $L->back
					]
				),
				[
					'style'	=> 'display: none;'
				]
			).
			h::{'div.cs-header-registration-form'}(
				h::{'input.cs-noui.cs-header-registration-email[tabindex=1]'}(
					[
						'placeholder'	=> $L->email
					]
				).
				h::{'button.cs-header-registration-process.cs-button-compact[tabindex=2]'}(
					h::icon('pencil').$L->registration
				).
				h::div().
				h::{'button.cs-button-compact.cs-header-back[tabindex=4]'}(
					h::icon('carat-1-s'),
					[
						'data-title'	=> $L->back
					]
				),
				[
					'style'	=> 'display: none;'
				]
			).
			h::{'div.cs-header-login-form'}(
				h::{'input.cs-noui.cs-header-login-email[tabindex=1]'}([
					'placeholder'	=> $L->login_or_email
				]).
				h::{'input.cs-noui.cs-header-user-password[type=password][tabindex=2]'}([
					'placeholder'	=> $L->password
				]).
				h::{'button.cs-header-login-process.cs-button-compact[tabindex=3]'}(h::icon('check').$L->log_in).
				h::{'button.cs-button-compact.cs-header-back[tabindex=5]'}(
					h::icon('carat-1-s'),
					[
						'data-title'	=> $L->back
					]
				).
				h::{'button.cs-button-compact.cs-header-restore-password-slide[tabindex=4]'}(
					h::icon('help'),
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
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
	/**
	 * Page generation
	 */
	function __finish () {
		global $Config;
		/**
		 * Cleaning of output
		 */
		if (OUT_CLEAN) {
			ob_end_clean();
		}
		/**
		 * For AJAX and API requests only content without page template
		 */
		if (!$this->interface) {
			/**
			 * Processing of replacing in content
			 */
			echo $this->process_replacing($this->Content);
		} else {
			global $Error, $L, $timeload, $User, $Core;
			$Core->run_trigger('System/Page/pre_display');
			is_object($Error) && $Error->display();
			/**
			 * Processing of template, substituting of content, preparing for the output
			 */
			$this->prepare();
			/**
			 * Processing of replacing in content
			 */
			$this->Html = $this->process_replacing($this->Html);
			/**
			 * Detection of compression
			 */
			$ob = false;
			if (is_object($Config) && !zlib_compression() && $Config->core['gzip_compression'] && (is_object($Error) && !$Error->num())) {
				ob_start('ob_gzhandler');
				$ob = true;
			}
			$timeload['end'] = microtime(true);
			/**
			 * Getting of debug information
			 */
			if (
				is_object($User) && (
					$User->admin() || (
						$Config->can_be_admin && $Config->core['ip_admin_list_only']
					)
				) && DEBUG
			) {
				$this->get_debug_info();
			}
			echo str_replace(
				[
					'<!--debug_info-->',
					'<!--generate time-->',
					'<!--peak memory usage-->'
				],
				[
					$this->debug_info ? h::level(
						h::{'div#debug'}(
							h::level($this->debug_info),
							[
								'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
								'title'			=> $L->debug,
								'style'			=> 'display: none;'
							]
						),
						$this->level['debug_info']
					) : '',
					format_time(round($timeload['end'] - $timeload['start'], 5)),
					format_filesize(memory_get_peak_usage(), 5)
				],
				$this->Html
			);
			if ($ob) {
				ob_end_flush();
			}
		}
	}
}
/**
 * For IDE
 */
if (false) {
	global $Page;
	$Page = new Page;
}