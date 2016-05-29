<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page;
use
	cs\App,
	cs\Config,
	cs\Language,
	cs\Request,
	cs\Response,
	cs\User,
	h,
	cs\Page\Includes\Cache,
	cs\Page\Includes\Collecting,
	cs\Page\Includes\RequireJS;

/**
 * Includes management for `cs\Page` class
 *
 * @property string $Title
 * @property string $Description
 * @property string $canonical_url
 * @property string $Head
 * @property string $post_Body
 * @property string $theme
 */
trait Includes {
	use
		Cache,
		Collecting,
		RequireJS;
	protected $extension_to_as = [
		'jpeg' => 'image',
		'jpe'  => 'image',
		'jpg'  => 'image',
		'gif'  => 'image',
		'png'  => 'image',
		'svg'  => 'image',
		'svgz' => 'image',
		'woff' => 'font',
		//'woff2' => 'font',
		'css'  => 'style',
		'js'   => 'script',
		'html' => 'document'
	];
	/**
	 * @var array
	 */
	protected $core_html;
	/**
	 * @var array
	 */
	protected $core_js;
	/**
	 * @var array
	 */
	protected $core_css;
	/**
	 * @var string
	 */
	protected $core_config;
	/**
	 * @var array
	 */
	protected $html;
	/**
	 * @var array
	 */
	protected $js;
	/**
	 * @var array
	 */
	protected $css;
	/**
	 * @var string
	 */
	protected $config;
	/**
	 * Base name is used as prefix when creating CSS/JS/HTML cache files in order to avoid collisions when having several themes and languages
	 * @var string
	 */
	protected $pcache_basename_path;
	protected function init_includes () {
		$this->core_html            = ['path' => []]; // No plain HTML in core
		$this->core_js              = ['path' => []]; // No plain JS in core
		$this->core_css             = ['path' => []]; // No plain CSS in core
		$this->core_config          = '';
		$this->html                 = ['path' => [], 'plain' => ''];
		$this->js                   = ['path' => [], 'plain' => ''];
		$this->css                  = ['path' => [], 'plain' => ''];
		$this->config               = '';
		$this->pcache_basename_path = '';
	}
	/**
	 * Including of Web Components
	 *
	 * @param string|string[] $add  Path to including file, or code
	 * @param string          $mode Can be <b>file</b> or <b>code</b>
	 *
	 * @return \cs\Page
	 */
	function html ($add, $mode = 'file') {
		return $this->html_internal($add, $mode);
	}
	/**
	 * @param string|string[] $add
	 * @param string          $mode
	 * @param bool            $core
	 *
	 * @return \cs\Page
	 */
	protected function html_internal ($add, $mode = 'file', $core = false) {
		return $this->include_common('html', $add, $mode, $core);
	}
	/**
	 * Including of JavaScript
	 *
	 * @param string|string[] $add  Path to including file, or code
	 * @param string          $mode Can be <b>file</b> or <b>code</b>
	 *
	 * @return \cs\Page
	 */
	function js ($add, $mode = 'file') {
		return $this->js_internal($add, $mode);
	}
	/**
	 * @param string|string[] $add
	 * @param string          $mode
	 * @param bool            $core
	 *
	 * @return \cs\Page
	 */
	protected function js_internal ($add, $mode = 'file', $core = false) {
		return $this->include_common('js', $add, $mode, $core);
	}
	/**
	 * Including of CSS
	 *
	 * @param string|string[] $add  Path to including file, or code
	 * @param string          $mode Can be <b>file</b> or <b>code</b>
	 *
	 * @return \cs\Page
	 */
	function css ($add, $mode = 'file') {
		return $this->css_internal($add, $mode);
	}
	/**
	 * @param string|string[] $add
	 * @param string          $mode
	 * @param bool            $core
	 *
	 * @return \cs\Page
	 */
	protected function css_internal ($add, $mode = 'file', $core = false) {
		return $this->include_common('css', $add, $mode, $core);
	}
	/**
	 * @param string          $what
	 * @param string|string[] $add
	 * @param string          $mode
	 * @param bool            $core
	 *
	 * @return \cs\Page
	 */
	protected function include_common ($what, $add, $mode, $core) {
		if (!$add) {
			return $this;
		}
		if (is_array($add)) {
			foreach (array_filter($add) as $style) {
				$this->include_common($what, $style, $mode, $core);
			}
		} else {
			if ($core) {
				$what = "core_$what";
			}
			$target = &$this->$what;
			if ($mode == 'file') {
				$target['path'][] = $add;
			} elseif ($mode == 'code') {
				$target['plain'] .= "$add\n";
			}
		}
		return $this;
	}
	/**
	 * Add config on page to make it available on frontend
	 *
	 * @param mixed  $config_structure        Any scalar type or array
	 * @param string $target                  Target is property of `window` object where config will be inserted as value, nested properties like `cs.sub.prop`
	 *                                        are supported and all nested properties are created on demand. It is recommended to use sub-properties of `cs`
	 *
	 * @return \cs\Page
	 */
	function config ($config_structure, $target) {
		return $this->config_internal($config_structure, $target);
	}
	/**
	 * @param mixed  $config_structure
	 * @param string $target
	 * @param bool   $core
	 *
	 * @return \cs\Page
	 */
	protected function config_internal ($config_structure, $target, $core = false) {
		$config = h::script(
			json_encode($config_structure, JSON_UNESCAPED_UNICODE),
			[
				'target' => $target,
				'class'  => 'cs-config',
				'type'   => 'application/json'
			]
		);
		if ($core) {
			$this->core_config .= $config;
		} else {
			$this->config .= $config;
		}
		return $this;
	}
	/**
	 * Getting of HTML, JS and CSS includes
	 *
	 * @return \cs\Page
	 */
	protected function add_includes_on_page () {
		$Config = Config::instance(true);
		if (!$Config) {
			return $this;
		}
		/**
		 * Base name for cache files
		 */
		$this->pcache_basename_path = PUBLIC_CACHE.'/'.$this->theme.'_'.Language::instance()->clang;
		// TODO: I hope some day we'll get rid of this sh*t :(
		$this->ie_edge();
		$Request = Request::instance();
		/**
		 * If CSS and JavaScript compression enabled
		 */
		if ($this->page_compression_usage($Config, $Request)) {
			/**
			 * Rebuilding HTML, JS and CSS cache if necessary
			 */
			$this->rebuild_cache($Config);
			$this->webcomponents_polyfill($Request, true);
			list($includes, $preload) = $this->get_includes_and_preload_resource_for_page_with_compression($Request);
		} else {
			$this->webcomponents_polyfill($Request, false);
			/**
			 * Language translation is added explicitly only when compression is disabled, otherwise it will be in compressed JS file
			 */
			$this->config_internal(Language::instance(), 'cs.Language', true);
			$this->config_internal($this->get_requirejs_paths(), 'requirejs.paths', true);
			$includes = $this->get_includes_for_page_without_compression($Config, $Request);
			$preload  = [];
		}
		$this->css_internal($includes['css'], 'file', true);
		$this->js_internal($includes['js'], 'file', true);
		$this->html_internal($includes['html'], 'file', true);
		$this->add_includes_on_page_manually_added($Config, $Request, $preload);
		return $this;
	}
	/**
	 * @param Config  $Config
	 * @param Request $Request
	 *
	 * @return bool
	 */
	protected function page_compression_usage ($Config, $Request) {
		return $Config->core['cache_compress_js_css'] && !($Request->admin_path && isset($Request->query['debug']));
	}
	/**
	 * Add JS polyfills for IE/Edge
	 */
	protected function ie_edge () {
		if (!preg_match('/Trident|Edge/', Request::instance()->header('user-agent'))) {
			return;
		}
		$this->js_internal(
			get_files_list(DIR."/includes/js/microsoft_sh*t", "/.*\\.js$/i", 'f', "includes/js/microsoft_sh*t", true),
			'file',
			true
		);
	}
	/**
	 * Hack: Add WebComponents Polyfill for browsers without native Shadow DOM support
	 *
	 * @param Request $Request
	 * @param bool    $with_compression
	 */
	protected function webcomponents_polyfill ($Request, $with_compression) {
		if ($Request->cookie('shadow_dom') == 1) {
			return;
		}
		if ($with_compression) {
			$hash = file_get_contents(PUBLIC_CACHE.'/webcomponents.js.hash');
			$this->js_internal("/storage/pcache/webcomponents.js?$hash", 'file', true);
		} else {
			$this->js_internal('/includes/js/WebComponents-polyfill/webcomponents-custom.min.js', 'file', true);
		}
	}
	/**
	 * @param Request $Request
	 *
	 * @return array[]
	 */
	protected function get_includes_and_preload_resource_for_page_with_compression ($Request) {
		list($dependencies, $compressed_includes_map, $not_embedded_resources_map) = file_get_json("$this->pcache_basename_path.json");
		$includes = $this->get_normalized_includes($dependencies, $compressed_includes_map, $Request);
		$preload  = [];
		foreach (array_merge(...array_values($includes)) as $path) {
			$preload[] = [$path];
			if (isset($not_embedded_resources_map[$path])) {
				$preload[] = $not_embedded_resources_map[$path];
			}
		}
		return [$includes, array_merge(...$preload)];
	}
	/**
	 * @param array      $dependencies
	 * @param string[][] $includes_map
	 * @param Request    $Request
	 *
	 * @return string[][]
	 */
	protected function get_normalized_includes ($dependencies, $includes_map, $Request) {
		$current_module = $Request->current_module;
		/**
		 * Current URL based on controller path (it better represents how page was rendered)
		 */
		$current_url = array_slice(App::instance()->controller_path, 1);
		$current_url = ($Request->admin_path ? "admin/" : '')."$current_module/".implode('/', $current_url);
		/**
		 * Narrow the dependencies to current module only
		 */
		$dependencies    = array_unique(
			array_merge(
				['System'],
				$dependencies['System'],
				isset($dependencies[$current_module]) ? $dependencies[$current_module] : []
			)
		);
		$system_includes = [];
		// Array with empty array in order to avoid `array_merge()` failure later
		$dependencies_includes = array_fill_keys($dependencies, [[]]);
		$includes              = [];
		foreach ($includes_map as $path => $local_includes) {
			if ($path == 'System') {
				$system_includes = $local_includes;
			} elseif ($component = $this->get_dependency_component($dependencies, $path, $Request)) {
				/**
				 * @var string $component
				 */
				$dependencies_includes[$component][] = $local_includes;
			} elseif (mb_strpos($current_url, $path) === 0) {
				$includes[] = $local_includes;
			}
		}
		// Convert to indexed array first
		$dependencies_includes = array_values($dependencies_includes);
		// Flatten array on higher level
		$dependencies_includes = array_merge(...$dependencies_includes);
		// Hack: 2 array_merge_recursive() just to be compatible with HHVM, simplify when https://github.com/facebook/hhvm/issues/7087 is resolved
		return _array(array_merge_recursive(array_merge_recursive($system_includes, ...$dependencies_includes), ...$includes));
	}
	/**
	 * @param array   $dependencies
	 * @param string  $url
	 * @param Request $Request
	 *
	 * @return false|string
	 */
	protected function get_dependency_component ($dependencies, $url, $Request) {
		$url_exploded = explode('/', $url);
		/** @noinspection NestedTernaryOperatorInspection */
		$url_component = $url_exploded[0] != 'admin' ? $url_exploded[0] : (@$url_exploded[1] ?: '');
		$is_dependency =
			$url_component !== Config::SYSTEM_MODULE &&
			in_array($url_component, $dependencies) &&
			(
				$Request->admin_path || $Request->admin_path == ($url_exploded[0] == 'admin')
			);
		return $is_dependency ? $url_component : false;
	}
	/**
	 * @param Config  $Config
	 * @param Request $Request
	 *
	 * @return string[][]
	 */
	protected function get_includes_for_page_without_compression ($Config, $Request) {
		// To determine all dependencies and stuff we need `$Config` object to be already created
		list($dependencies, $includes_map) = $this->get_includes_dependencies_and_map($Config);
		$includes = $this->get_normalized_includes($dependencies, $includes_map, $Request);
		return $this->add_versions_hash($this->absolute_path_to_relative($includes));
	}
	/**
	 * @param string[]|string[][] $path
	 *
	 * @return string[]|string[][]
	 */
	protected function absolute_path_to_relative ($path) {
		return _substr($path, strlen(DIR));
	}
	/**
	 * @param string[][] $includes
	 *
	 * @return string[][]
	 */
	protected function add_versions_hash ($includes) {
		$content     = array_reduce(
			get_files_list(DIR.'/components', '/^meta\.json$/', 'f', true, true),
			function ($content, $file) {
				return $content.file_get_contents($file);
			}
		);
		$content_md5 = substr(md5($content), 0, 5);
		foreach ($includes as &$files) {
			foreach ($files as &$file) {
				$file .= "?$content_md5";
			}
			unset($file);
		}
		return $includes;
	}
	/**
	 * @param Config   $Config
	 * @param Request  $Request
	 * @param string[] $preload
	 */
	protected function add_includes_on_page_manually_added ($Config, $Request, $preload) {
		/** @noinspection NestedTernaryOperatorInspection */
		$this->Head .=
			array_reduce(
				array_merge($this->core_css['path'], $this->css['path']),
				function ($content, $href) {
					return "$content<link href=\"$href\" rel=\"stylesheet\" shim-shadowdom>\n";
				}
			).
			h::style($this->css['plain'] ?: false);
		if ($this->page_compression_usage($Config, $Request) && $Config->core['frontend_load_optimization']) {
			$this->add_includes_on_page_manually_added_frontend_load_optimization($Config);
		} else {
			$this->add_includes_on_page_manually_added_normal($Config, $Request, $preload);
		}
	}
	/**
	 * @param Config   $Config
	 * @param Request  $Request
	 * @param string[] $preload
	 */
	protected function add_includes_on_page_manually_added_normal ($Config, $Request, $preload) {
		$jquery    = $this->jquery($this->page_compression_usage($Config, $Request));
		$preload[] = $jquery;
		$this->add_preload($preload);
		$configs      = $this->core_config.$this->config;
		$scripts      =
			array_reduce(
				array_merge([$jquery], $this->core_js['path'], $this->js['path']),
				function ($content, $src) {
					return "$content<script src=\"$src\"></script>\n";
				}
			).
			h::script($this->js['plain'] ?: false);
		$html_imports =
			array_reduce(
				array_merge($this->core_html['path'], $this->html['path']),
				function ($content, $href) {
					return "$content<link href=\"$href\" rel=\"import\">\n";
				}
			).
			$this->html['plain'];
		$this->Head .= $configs;
		if ($Config->core['put_js_after_body']) {
			$this->post_Body .= $scripts.$html_imports;
		} else {
			$this->Head .= $scripts.$html_imports;
		}
	}
	/**
	 * Hack: jQuery is kind of special; it is only loaded directly in normal mode, during frontend load optimization it is loaded asynchronously in frontend
	 * TODO: In future we'll load jQuery as AMD module only and this thing will not be needed
	 *
	 * @param bool $with_compression
	 *
	 * @return string
	 */
	protected function jquery ($with_compression) {
		if ($with_compression) {
			$hash = file_get_contents(PUBLIC_CACHE.'/jquery.js.hash');
			return "/storage/pcache/jquery.js?$hash";
		} else {
			return '/includes/js/jquery/jquery-3.0.0-pre.js';
		}
	}
	/**
	 * @param string[] $preload
	 */
	protected function add_preload ($preload) {
		$Response = Response::instance();
		foreach ($preload as $resource) {
			$extension = explode('?', file_extension($resource))[0];
			$as        = $this->extension_to_as[$extension];
			$resource  = str_replace(' ', '%20', $resource);
			$Response->header('Link', "<$resource>; rel=preload; as=$as", false);
		}
	}
	/**
	 * @param Config $Config
	 */
	protected function add_includes_on_page_manually_added_frontend_load_optimization ($Config) {
		list($optimized_includes, $preload) = file_get_json("$this->pcache_basename_path.optimized.json");
		$this->add_preload(
			array_unique(
				array_merge(
					$preload,
					$this->core_css['path'],
					$this->css['path']
				)
			)
		);
		$system_scripts    = '';
		$optimized_scripts = [$this->jquery(true)];
		$system_imports    = '';
		$optimized_imports = [];
		foreach (array_merge($this->core_js['path'], $this->js['path']) as $script) {
			if (isset($optimized_includes[$script])) {
				$optimized_scripts[] = $script;
			} else {
				$system_scripts .= "<script src=\"$script\"></script>\n";
			}
		}
		foreach (array_merge($this->core_html['path'], $this->html['path']) as $import) {
			if (isset($optimized_includes[$import])) {
				$optimized_imports[] = $import;
			} else {
				$system_imports .= "<link href=\"$import\" rel=\"import\">\n";
			}
		}
		$scripts      = h::script($this->js['plain'] ?: false);
		$html_imports = $this->html['plain'];
		$this->config([$optimized_scripts, $optimized_imports], 'cs.optimized_includes');
		$this->Head .= $this->core_config.$this->config;
		if ($Config->core['put_js_after_body']) {
			$this->post_Body .= $system_scripts.$system_imports.$scripts.$html_imports;
		} else {
			$this->Head .= $system_scripts.$system_imports.$scripts.$html_imports;
		}
	}
}
