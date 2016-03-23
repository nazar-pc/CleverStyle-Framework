<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page;
use
	cs\App,
	cs\Core,
	cs\Config,
	cs\Event,
	cs\Language,
	cs\Request,
	cs\User,
	h;

/**
 * Provides next events:
 *  System/Page/includes_dependencies_and_map
 *  [
 *    'dependencies' => &$dependencies,
 *    'includes_map' => &$includes_map
 *  ]
 *
 *  System/Page/rebuild_cache
 *  [
 *    'key' => &$key //Reference to the key, that will be appended to all css and js files, can be changed to reflect JavaScript and CSS changes
 *  ]
 *
 *  System/Page/requirejs
 *  [
 *    'paths'                 => &$paths,                // The same as `paths` in requirejs.config()
 *    'directories_to_browse' => &$directories_to_browse // Where to look for AMD modules (typically bower_components and node_modules directories)
 *  ]
 *
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
	/**
	 * @var array[]
	 */
	protected $core_html;
	/**
	 * @var array[]
	 */
	protected $core_js;
	/**
	 * @var array[]
	 */
	protected $core_css;
	/**
	 * @var string
	 */
	protected $core_config;
	/**
	 * @var array[]
	 */
	protected $html;
	/**
	 * @var array[]
	 */
	protected $js;
	/**
	 * @var array[]
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
	protected $pcache_basename;
	protected function init_includes () {
		$this->core_html       = [0 => [], 1 => []];
		$this->core_js         = [0 => [], 1 => []];
		$this->core_css        = [0 => [], 1 => []];
		$this->core_config     = '';
		$this->html            = [0 => [], 1 => []];
		$this->js              = [0 => [], 1 => []];
		$this->css             = [0 => [], 1 => []];
		$this->config          = '';
		$this->pcache_basename = '';
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
		if (!$add) {
			return $this;
		}
		if (is_array($add)) {
			foreach (array_filter($add) as $script) {
				$this->html_internal($script, $mode, $core);
			}
		} else {
			if ($core) {
				$html = &$this->core_html;
			} else {
				$html = &$this->html;
			}
			if ($mode == 'file') {
				$html[0][] = h::link(
					[
						'href' => $add,
						'rel'  => 'import'
					]
				);
			} elseif ($mode == 'code') {
				$html[1][] = "$add\n";
			}
		}
		return $this;
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
		if (!$add) {
			return $this;
		}
		if (is_array($add)) {
			foreach (array_filter($add) as $script) {
				$this->js_internal($script, $mode, $core);
			}
		} else {
			if ($core) {
				$js = &$this->core_js;
			} else {
				$js = &$this->js;
			}
			if ($mode == 'file') {
				$js[0][] = h::script(
					[
						'src' => $add
					]
				);
			} elseif ($mode == 'code') {
				$js[1][] = "$add\n";
			}
		}
		return $this;
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
		if (!$add) {
			return $this;
		}
		if (is_array($add)) {
			foreach (array_filter($add) as $style) {
				$this->css_internal($style, $mode, $core);
			}
		} else {
			if ($core) {
				$css = &$this->core_css;
			} else {
				$css = &$this->css;
			}
			if ($mode == 'file') {
				$css[0][] = h::link(
					[
						'href'           => $add,
						'rel'            => 'stylesheet',
						'shim-shadowdom' => true
					]
				);
			} elseif ($mode == 'code') {
				$css[1][] = "$add\n";
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
		$this->pcache_basename = "_{$this->theme}_".Language::instance()->clang;
		/**
		 * Some JS configs required by system
		 */
		$this->add_system_configs();
		// TODO: I hope some day we'll get rid of this sh*t :(
		$this->ie_edge();
		/**
		 * If CSS and JavaScript compression enabled
		 */
		if ($Config->core['cache_compress_js_css'] && !(Request::instance()->admin_path && isset($_GET['debug']))) {
			$this->webcomponents_polyfill(true);
			$includes = $this->get_includes_for_page_with_compression();
		} else {
			$this->webcomponents_polyfill(false);
			/**
			 * Language translation is added explicitly only when compression is disabled, otherwise it will be in compressed JS file
			 */
			/**
			 * @var \cs\Page $this
			 */
			$this->config_internal(Language::instance(), 'cs.Language', true);
			$this->config_internal($this->get_requirejs_paths(), 'requirejs.paths', true);
			$includes = $this->get_includes_for_page_without_compression($Config);
		}
		$this->css_internal($includes['css'], 'file', true);
		$this->js_internal($includes['js'], 'file', true);
		$this->html_internal($includes['html'], 'file', true);
		$this->add_includes_on_page_manually_added($Config);
		return $this;
	}
	/**
	 * @return string[]
	 */
	protected function get_requirejs_paths () {
		$Config = Config::instance();
		$paths  = [];
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			$this->get_requirejs_paths_add_aliases(MODULES."/$module_name", $paths);
		}
		foreach ($Config->components['plugins'] as $plugin_name) {
			$this->get_requirejs_paths_add_aliases(PLUGINS."/$plugin_name", $paths);
		}
		$directories_to_browse = [
			DIR.'/bower_components',
			DIR.'/node_modules'
		];
		Event::instance()->fire(
			'System/Page/requirejs',
			[
				'paths'                 => &$paths,
				'directories_to_browse' => &$directories_to_browse
			]
		);
		foreach ($directories_to_browse as $dir) {
			foreach (get_files_list($dir, false, 'd', true) as $d) {
				$this->get_requirejs_paths_find_package($d, $paths);
			}
		}
		return $paths;
	}
	/**
	 * @param string   $dir
	 * @param string[] $paths
	 */
	protected function get_requirejs_paths_add_aliases ($dir, &$paths) {
		if (is_dir("$dir/includes/js")) {
			$name         = basename($dir);
			$paths[$name] = $this->absolute_path_to_relative("$dir/includes/js");
			foreach ((array)@file_get_json("$dir/meta.json")['provide'] as $p) {
				if (strpos($p, '/') !== false) {
					$paths[$p] = $paths[$name];
				}
			}
		}
	}
	/**
	 * @param string   $dir
	 * @param string[] $paths
	 */
	protected function get_requirejs_paths_find_package ($dir, &$paths) {
		$path = $this->get_requirejs_paths_find_package_bower($dir) ?: $this->get_requirejs_paths_find_package_npm($dir);
		if ($path) {
			$paths[basename($dir)] = $this->absolute_path_to_relative(substr($path, 0, -3));
		}
	}
	/**
	 * @param string $dir
	 *
	 * @return string
	 */
	protected function get_requirejs_paths_find_package_bower ($dir) {
		$bower = @file_get_json("$dir/bower.json");
		foreach (@(array)$bower['main'] as $main) {
			if (preg_match('/\.js$/', $main)) {
				$main = substr($main, 0, -3);
				// There is a chance that minified file is present
				$main = file_exists_with_extension("$dir/$main", ['min.js', 'js']);
				if ($main) {
					return $main;
				}
			}
		}
		return null;
	}
	/**
	 * @param string $dir
	 *
	 * @return false|string
	 */
	protected function get_requirejs_paths_find_package_npm ($dir) {
		$package = @file_get_json("$dir/package.json");
		// If we have browser-specific declaration - use it
		$main = @$package['browser'] ?: (@$package['jspm']['main'] ?: @$package['main']);
		if (preg_match('/\.js$/', $main)) {
			$main = substr($main, 0, -3);
		}
		if ($main) {
			// There is a chance that minified file is present
			return file_exists_with_extension("$dir/$main", ['min.js', 'js']) ?: file_exists_with_extension("$dir/dist/$main", ['min.js', 'js']);
		}
	}
	/**
	 * Since modules, plugins and storage directories can be (at least theoretically) moved from default location - let's do proper path conversion
	 *
	 * @param string|string[] $path
	 *
	 * @return string|string[]
	 */
	protected function absolute_path_to_relative ($path) {
		if (is_array($path)) {
			foreach ($path as &$p) {
				$p = $this->absolute_path_to_relative($p);
			}
			return $path;
		}
		if (strpos($path, MODULES) === 0) {
			return 'components/modules'.substr($path, strlen(MODULES));
		}
		if (strpos($path, PLUGINS) === 0) {
			return 'components/plugins'.substr($path, strlen(PLUGINS));
		}
		if (strpos($path, STORAGE) === 0) {
			return 'storage'.substr($path, strlen(STORAGE));
		}
		return substr($path, strlen(DIR) + 1);
	}
	/**
	 * Add JS polyfills for IE/Edge
	 */
	protected function ie_edge () {
		if (preg_match('/Trident|Edge/', Request::instance()->header('user-agent'))) {
			$this->js_internal(
				get_files_list(DIR."/includes/js/microsoft_sh*t", "/.*\\.js$/i", 'f', "includes/js/microsoft_sh*t", true),
				'file',
				true
			);
		}
	}
	/**
	 * Hack: Add WebComponents Polyfill for browsers without native Shadow DOM support
	 *
	 * TODO: Probably, some effective User Agent-based check might be used here
	 *
	 * @param bool $with_compression
	 */
	protected function webcomponents_polyfill ($with_compression) {
		if (!isset($_COOKIE['shadow_dom']) || $_COOKIE['shadow_dom'] != 1) {
			$file = 'includes/js/WebComponents-polyfill/webcomponents-custom.min.js';
			if ($with_compression) {
				$compressed_file = PUBLIC_CACHE.'/webcomponents.js';
				if (!file_exists($compressed_file)) {
					$content = file_get_contents(DIR."/$file");
					file_put_contents($compressed_file, gzencode($content, 9), LOCK_EX | FILE_BINARY);
					file_put_contents("$compressed_file.hash", substr(md5($content), 0, 5));
				}
				$hash = file_get_contents("$compressed_file.hash");
				$this->js_internal("storage/pcache/webcomponents.js?$hash", 'file', true);
			} else {
				$this->js_internal($file, 'file', true);
			}
		}
	}
	protected function add_system_configs () {
		$Config         = Config::instance();
		$Request        = Request::instance();
		$User           = User::instance();
		$current_module = $Request->current_module;
		$this->config_internal(
			[
				'base_url'              => $Config->base_url(),
				'current_base_url'      => $Config->base_url().'/'.($Request->admin_path ? 'admin/' : '').$current_module,
				'public_key'            => Core::instance()->public_key,
				'module'                => $current_module,
				'in_admin'              => (int)$Request->admin_path,
				'is_admin'              => (int)$User->admin(),
				'is_user'               => (int)$User->user(),
				'is_guest'              => (int)$User->guest(),
				'password_min_length'   => (int)$Config->core['password_min_length'],
				'password_min_strength' => (int)$Config->core['password_min_strength'],
				'debug'                 => (int)DEBUG,
				'route'                 => $Request->route,
				'route_path'            => $Request->route_path,
				'route_ids'             => $Request->route_ids
			],
			'cs',
			true
		);
		if ($User->admin()) {
			$this->config_internal((int)$Config->core['simple_admin_mode'], 'cs.simple_admin_mode', true);
		}
	}
	/**
	 * @return array[]
	 */
	protected function get_includes_for_page_with_compression () {
		/**
		 * Rebuild cache if necessary
		 */
		if (!file_exists(PUBLIC_CACHE."/$this->pcache_basename.json")) {
			$this->rebuild_cache();
		}
		list($dependencies, $structure) = file_get_json(PUBLIC_CACHE."/$this->pcache_basename.json");
		$system_includes = [
			'css'  => ["storage/pcache/$this->pcache_basename.css?{$structure['']['css']}"],
			'js'   => ["storage/pcache/$this->pcache_basename.js?{$structure['']['js']}"],
			'html' => ["storage/pcache/$this->pcache_basename.html?{$structure['']['html']}"]
		];
		list($includes, $dependencies_includes, $dependencies, $current_url) = $this->get_includes_prepare($dependencies, '+');
		foreach ($structure as $filename_prefix => $hashes) {
			if (!$filename_prefix) {
				continue;
			}
			$is_dependency = $this->get_includes_is_dependency($dependencies, $filename_prefix, '+');
			if ($is_dependency || mb_strpos($current_url, $filename_prefix) === 0) {
				foreach ($hashes as $extension => $hash) {
					if ($is_dependency) {
						$dependencies_includes[$extension][] = "storage/pcache/$filename_prefix$this->pcache_basename.$extension?$hash";
					} else {
						$includes[$extension][] = "storage/pcache/$filename_prefix$this->pcache_basename.$extension?$hash";
					}
				}
			}
		}
		return array_merge_recursive($system_includes, $dependencies_includes, $includes);
	}
	/**
	 * @param Config $Config
	 *
	 * @return array[]
	 */
	protected function get_includes_for_page_without_compression ($Config) {
		// To determine all dependencies and stuff we need `$Config` object to be already created
		if ($Config) {
			list($dependencies, $includes_map) = $this->includes_dependencies_and_map();
			$system_includes = $includes_map[''];
			list($includes, $dependencies_includes, $dependencies, $current_url) = $this->get_includes_prepare($dependencies, '/');
			foreach ($includes_map as $url => $local_includes) {
				if (!$url) {
					continue;
				}
				$is_dependency = $this->get_includes_is_dependency($dependencies, $url, '/');
				if ($is_dependency) {
					$dependencies_includes = array_merge_recursive($dependencies_includes, $local_includes);
				} elseif (mb_strpos($current_url, $url) === 0) {
					$includes = array_merge_recursive($includes, $local_includes);
				}
			}
			$includes = array_merge_recursive($system_includes, $dependencies_includes, $includes);
			$includes = $this->absolute_path_to_relative($includes);
		} else {
			$includes = $this->get_includes_list();
		}
		return $this->add_versions_hash($includes);
	}
	/**
	 * @param array  $dependencies
	 * @param string $separator `+` or `/`
	 *
	 * @return array
	 */
	protected function get_includes_prepare ($dependencies, $separator) {
		$Request               = Request::instance();
		$includes              = [
			'css'  => [],
			'js'   => [],
			'html' => []
		];
		$dependencies_includes = $includes;
		$current_module        = $Request->current_module;
		/**
		 * Current URL based on controller path (it better represents how page was rendered)
		 */
		$current_url = array_slice(App::instance()->controller_path, 1);
		$current_url = ($Request->admin_path ? "admin$separator" : '')."$current_module$separator".implode($separator, $current_url);
		/**
		 * Narrow the dependencies to current module only
		 */
		$dependencies = array_merge(
			isset($dependencies[$current_module]) ? $dependencies[$current_module] : [],
			$dependencies['System']
		);
		return [$includes, $dependencies_includes, $dependencies, $current_url];
	}
	/**
	 * @param array  $dependencies
	 * @param string $url
	 * @param string $separator `+` or `/`
	 *
	 * @return bool
	 */
	protected function get_includes_is_dependency ($dependencies, $url, $separator) {
		$url_exploded = explode($separator, $url);
		/** @noinspection NestedTernaryOperatorInspection */
		$url_module = $url_exploded[0] != 'admin' ? $url_exploded[0] : (@$url_exploded[1] ?: '');
		$Request    = Request::instance();
		return
			$url_module !== Config::SYSTEM_MODULE &&
			in_array($url_module, $dependencies) &&
			(
				$Request->admin_path || $Request->admin_path == ($url_exploded[0] == 'admin')
			);
	}
	protected function add_versions_hash ($includes) {
		$content = '';
		foreach (get_files_list(MODULES, false, 'd') as $module) {
			if (file_exists(MODULES."/$module/meta.json")) {
				$content .= file_get_contents(MODULES."/$module/meta.json");
			}
		}
		foreach (get_files_list(PLUGINS, false, 'd') as $plugin) {
			if (file_exists(PLUGINS."/$plugin/meta.json")) {
				$content .= file_get_contents(PLUGINS."/$plugin/meta.json");
			}
		}
		$hash = substr(md5($content), 0, 5);
		foreach ($includes as &$files) {
			foreach ($files as &$file) {
				$file .= "?$hash";
			}
			unset($file);
		}
		return $includes;
	}
	/**
	 * @param Config $Config
	 */
	protected function add_includes_on_page_manually_added ($Config) {
		foreach (['core_html', 'core_js', 'core_css', 'html', 'js', 'css'] as $type) {
			foreach ($this->$type as &$elements) {
				$elements = implode('', array_unique($elements));
			}
			unset($elements);
		}
		$this->Head .=
			$this->core_config.
			$this->config.
			$this->core_css[0].$this->css[0].
			h::style($this->core_css[1].$this->css[1] ?: false);
		$js_html_insert_to = $Config->core['put_js_after_body'] ? 'post_Body' : 'Head';
		$js_html           =
			$this->core_js[0].
			h::script($this->core_js[1] ?: false).
			$this->js[0].
			h::script($this->js[1] ?: false).
			$this->core_html[0].$this->html[0].
			$this->core_html[1].$this->html[1];
		$this->$js_html_insert_to .= $js_html;
	}
	/**
	 * Getting of HTML, JS and CSS files list to be included
	 *
	 * @param bool $absolute If <i>true</i> - absolute paths to files will be returned
	 *
	 * @return array
	 */
	protected function get_includes_list ($absolute = false) {
		$theme_dir  = THEMES."/$this->theme";
		$theme_pdir = "themes/$this->theme";
		$get_files  = function ($dir, $prefix_path) {
			$extension = basename($dir);
			$list      = get_files_list($dir, "/.*\\.$extension$/i", 'f', $prefix_path, true, 'name', '!include') ?: [];
			sort($list);
			return $list;
		};
		/**
		 * Get includes of system and theme
		 */
		$includes = [];
		foreach (['html', 'js', 'css'] as $type) {
			$includes[$type] = array_merge(
				$get_files(DIR."/includes/$type", $absolute ? true : "includes/$type"),
				$get_files("$theme_dir/$type", $absolute ? true : "$theme_pdir/$type")
			);
		}
		unset($theme_dir, $theme_pdir);
		$Config = Config::instance();
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			foreach (['html', 'js', 'css'] as $type) {
				/** @noinspection SlowArrayOperationsInLoopInspection */
				$includes[$type] = array_merge(
					$includes[$type],
					$get_files(MODULES."/$module_name/includes/$type", $absolute ? true : "components/modules/$module_name/includes/$type")
				);
			}
		}
		foreach ($Config->components['plugins'] as $plugin_name) {
			foreach (['html', 'js', 'css'] as $type) {
				/** @noinspection SlowArrayOperationsInLoopInspection */
				$includes[$type] = array_merge(
					$includes[$type],
					$get_files(PLUGINS."/$plugin_name/includes/$type", $absolute ? true : "components/plugins/$plugin_name/includes/$type")
				);
			}
		}
		return $includes;
	}
	/**
	 * Rebuilding of HTML, JS and CSS cache
	 *
	 * @return \cs\Page
	 */
	protected function rebuild_cache () {
		list($dependencies, $includes_map) = $this->includes_dependencies_and_map();
		$structure = [];
		foreach ($includes_map as $filename_prefix => $includes) {
			// We replace `/` by `+` to make it suitable for filename
			$filename_prefix             = str_replace('/', '+', $filename_prefix);
			$structure[$filename_prefix] = $this->create_cached_includes_files($filename_prefix, $includes);
		}
		unset($includes_map, $filename_prefix, $includes);
		file_put_json(
			PUBLIC_CACHE."/$this->pcache_basename.json",
			[$dependencies, $structure]
		);
		unset($structure);
		Event::instance()->fire('System/Page/rebuild_cache');
		return $this;
	}
	/**
	 * Creates cached version of given HTML, JS and CSS files.
	 * Resulting file name consists of <b>$filename_prefix</b> and <b>$this->pcache_basename</b>
	 *
	 * @param string $filename_prefix
	 * @param array  $includes Array of paths to files, may have keys: <b>css</b> and/or <b>js</b> and/or <b>html</b>
	 *
	 * @return array
	 */
	protected function create_cached_includes_files ($filename_prefix, $includes) {
		$cache_hash = [];
		/** @noinspection AlterInForeachInspection */
		foreach ($includes as $extension => $files) {
			$content = $this->create_cached_includes_files_process_files(
				$extension,
				$filename_prefix,
				$files
			);
			file_put_contents(PUBLIC_CACHE."/$filename_prefix$this->pcache_basename.$extension", gzencode($content, 9), LOCK_EX | FILE_BINARY);
			$cache_hash[$extension] = substr(md5($content), 0, 5);
		}
		return $cache_hash;
	}
	protected function create_cached_includes_files_process_files ($extension, $filename_prefix, $files) {
		$content = '';
		switch ($extension) {
			/**
			 * Insert external elements into resulting css file.
			 * It is needed, because those files will not be copied into new destination of resulting css file.
			 */
			case 'css':
				$callback = function ($content, $file) {
					return
						$content.
						Includes_processing::css(
							file_get_contents($file),
							$file
						);
				};
				break;
			/**
			 * Combine css and js files for Web Component into resulting files in order to optimize loading process
			 */
			case 'html':
				/**
				 * For CSP-compatible HTML files we need to know destination to put there additional JS/CSS files
				 */
				$destination = Config::instance()->core['vulcanization'] ? false : PUBLIC_CACHE;
				$callback    = function ($content, $file) use ($filename_prefix, $destination) {
					return
						$content.
						Includes_processing::html(
							file_get_contents($file),
							$file,
							"$filename_prefix$this->pcache_basename-".basename($file).'+'.substr(md5($file), 0, 5),
							$destination
						);
				};
				break;
			case 'js':
				$callback = function ($content, $file) {
					return
						$content.
						Includes_processing::js(file_get_contents($file));
				};
				if ($filename_prefix == '') {
					$content = 'window.cs={Language:'._json_encode(Language::instance()).'};';
					$content .= 'window.requirejs={paths:'._json_encode($this->get_requirejs_paths()).'};';
				}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return array_reduce(array_filter($files, 'file_exists'), $callback, $content);
	}
	/**
	 * Get dependencies of components between each other (only that contains some HTML, JS and CSS files) and mapping HTML, JS and CSS files to URL paths
	 *
	 * @return array[] [$dependencies, $includes_map]
	 */
	protected function includes_dependencies_and_map () {
		/**
		 * Get all includes
		 */
		$all_includes = $this->get_includes_list(true);
		$includes_map = [];
		/**
		 * Array [package => [list of packages it depends on]]
		 */
		$dependencies    = [];
		$functionalities = [];
		/**
		 * According to components's maps some files should be included only on specific pages.
		 * Here we read this rules, and remove from whole includes list such items, that should be included only on specific pages.
		 * Also collect dependencies.
		 */
		$Config = Config::instance();
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			if (file_exists(MODULES."/$module_name/meta.json")) {
				$this->process_meta(
					file_get_json(MODULES."/$module_name/meta.json"),
					$dependencies,
					$functionalities
				);
			}
			if (file_exists(MODULES."/$module_name/includes/map.json")) {
				$this->process_map(
					file_get_json_nocomments(MODULES."/$module_name/includes/map.json"),
					MODULES."/$module_name/includes",
					$includes_map,
					$all_includes
				);
			}
		}
		unset($module_name, $module_data);
		foreach ($Config->components['plugins'] as $plugin_name) {
			if (file_exists(PLUGINS."/$plugin_name/meta.json")) {
				$this->process_meta(
					file_get_json(PLUGINS."/$plugin_name/meta.json"),
					$dependencies,
					$functionalities
				);
			}
			if (file_exists(PLUGINS."/$plugin_name/includes/map.json")) {
				$this->process_map(
					file_get_json_nocomments(PLUGINS."/$plugin_name/includes/map.json"),
					PLUGINS."/$plugin_name/includes",
					$includes_map,
					$all_includes
				);
			}
		}
		unset($plugin_name);
		/**
		 * For consistency
		 */
		$includes_map[''] = $all_includes;
		Event::instance()->fire(
			'System/Page/includes_dependencies_and_map',
			[
				'dependencies' => &$dependencies,
				'includes_map' => &$includes_map
			]
		);
		$dependencies = $this->normalize_dependencies($dependencies, $functionalities);
		$includes_map = $this->clean_includes_arrays_without_files($dependencies, $includes_map);
		$dependencies = array_map('array_values', $dependencies);
		$dependencies = array_filter($dependencies);
		return [$dependencies, $includes_map];
	}
	/**
	 * Process meta information and corresponding entries to dependencies and functionalities
	 *
	 * @param array $meta
	 * @param array $dependencies
	 * @param array $functionalities
	 */
	protected function process_meta ($meta, &$dependencies, &$functionalities) {
		$package = $meta['package'];
		if (isset($meta['require'])) {
			foreach ((array)$meta['require'] as $r) {
				/**
				 * Get only name of package or functionality
				 */
				$r                        = preg_split('/[=<>]/', $r, 2)[0];
				$dependencies[$package][] = $r;
			}
		}
		if (isset($meta['optional'])) {
			foreach ((array)$meta['optional'] as $o) {
				/**
				 * Get only name of package or functionality
				 */
				$o                        = preg_split('/[=<>]/', $o, 2)[0];
				$dependencies[$package][] = $o;
			}
			unset($o);
		}
		if (isset($meta['provide'])) {
			foreach ((array)$meta['provide'] as $p) {
				/**
				 * If provides sub-functionality for other component (for instance, `Blog/post_patch`) - inverse "providing" to "dependency"
				 * Otherwise it is just functionality alias to package name
				 */
				if (strpos($p, '/') !== false) {
					/**
					 * Get name of package or functionality
					 */
					$p                  = explode('/', $p)[0];
					$dependencies[$p][] = $package;
				} else {
					$functionalities[$p] = $package;
				}
			}
			unset($p);
		}
	}
	/**
	 * Process map structure, fill includes map and remove files from list of all includes (remaining files will be included on all pages)
	 *
	 * @param array  $map
	 * @param string $includes_dir
	 * @param array  $includes_map
	 * @param array  $all_includes
	 */
	protected function process_map ($map, $includes_dir, &$includes_map, &$all_includes) {
		foreach ($map as $path => $files) {
			foreach ((array)$files as $file) {
				$extension = file_extension($file);
				if (in_array($extension, ['css', 'js', 'html'])) {
					$file                              = "$includes_dir/$extension/$file";
					$includes_map[$path][$extension][] = $file;
					$all_includes[$extension]          = array_diff($all_includes[$extension], [$file]);
				} else {
					$file = rtrim($file, '*');
					/**
					 * Wildcard support, it is possible to specify just path prefix and all files with this prefix will be included
					 */
					$found_files = array_filter(
						get_files_list($includes_dir, '/.*\.(css|js|html)$/i', 'f', '', true, 'name', '!include') ?: [],
						function ($f) use ($file) {
							// We need only files with specified mask and only those located in directory that corresponds to file's extension
							return preg_match("#^(css|js|html)/$file.*\\1$#i", $f);
						}
					);
					// Drop first level directory
					$found_files = _preg_replace('#^[^/]+/(.*)#', '$1', $found_files);
					$this->process_map([$path => $found_files], $includes_dir, $includes_map, $all_includes);
				}
			}
		}
	}
	/**
	 * Replace functionalities by real packages names, take into account recursive dependencies
	 *
	 * @param array $dependencies
	 * @param array $functionalities
	 *
	 * @return array
	 */
	protected function normalize_dependencies ($dependencies, $functionalities) {
		/**
		 * First of all remove packages without any dependencies
		 */
		$dependencies = array_filter($dependencies);
		/**
		 * First round, process aliases among keys
		 */
		foreach (array_keys($dependencies) as $d) {
			if (isset($functionalities[$d])) {
				$package = $functionalities[$d];
				/**
				 * Add dependencies to existing package dependencies
				 */
				foreach ($dependencies[$d] as $dependency) {
					$dependencies[$package][] = $dependency;
				}
				/**
				 * Drop alias
				 */
				unset($dependencies[$d]);
			}
		}
		unset($d, $dependency);
		/**
		 * Second round, process aliases among dependencies
		 */
		foreach ($dependencies as &$depends_on) {
			foreach ($depends_on as &$dependency) {
				if (isset($functionalities[$dependency])) {
					$dependency = $functionalities[$dependency];
				}
			}
		}
		unset($depends_on, $dependency);
		/**
		 * Third round, process recursive dependencies
		 */
		foreach ($dependencies as &$depends_on) {
			foreach ($depends_on as &$dependency) {
				if ($dependency != 'System' && isset($dependencies[$dependency])) {
					foreach (array_diff($dependencies[$dependency], $depends_on) as $new_dependency) {
						$depends_on[] = $new_dependency;
					}
				}
			}
		}
		return array_map('array_unique', $dependencies);
	}
	/**
	 * Includes array is composed from dependencies and sometimes dependencies doesn't have any files, so we'll clean that
	 *
	 * @param array $dependencies
	 * @param array $includes_map
	 *
	 * @return array
	 */
	protected function clean_includes_arrays_without_files ($dependencies, $includes_map) {
		foreach ($dependencies as &$depends_on) {
			foreach ($depends_on as $index => &$dependency) {
				if (!isset($includes_map[$dependency])) {
					unset($depends_on[$index]);
				}
			}
			unset($dependency);
		}
		return $includes_map;
	}
}
