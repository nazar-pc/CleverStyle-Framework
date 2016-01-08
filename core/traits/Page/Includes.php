<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page;
use
	cs\Core,
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Route,
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
	protected $core_html   = [0 => [], 1 => []];
	protected $core_js     = [0 => [], 1 => []];
	protected $core_css    = [0 => [], 1 => []];
	protected $core_config = '';
	protected $html        = [0 => [], 1 => []];
	protected $js          = [0 => [], 1 => []];
	protected $css         = [0 => [], 1 => []];
	protected $config      = '';
	/**
	 * Base name is used as prefix when creating CSS/JS/HTML cache files in order to avoid collisions when having several themes and languages
	 * @var string
	 */
	protected $pcache_basename;
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
		if ($Config->core['cache_compress_js_css'] && !(admin_path() && isset($_GET['debug']))) {
			$includes = $this->get_includes_for_page_with_compression();
		} else {
			/**
			 * Language translation is added explicitly only when compression is disabled, otherwise it will be in compressed JS file
			 */
			/**
			 * @var \cs\Page $this
			 */
			$this->config_internal(Language::instance(), 'cs.Language', true);
			$includes = $this->get_includes_for_page_without_compression($Config);
		}
		$this->css_internal($includes['css'], 'file', true);
		$this->js_internal($includes['js'], 'file', true);
		$this->html_internal($includes['html'], 'file', true);
		$this->add_includes_on_page_manually_added($Config);
		return $this;
	}
	/**
	 * Add JS polyfills for IE/Edge
	 */
	protected function ie_edge () {
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		if (preg_match('/Trident|Edge/', $_SERVER->user_agent)) {
			$this->js_internal(
				get_files_list(DIR."/includes/js/microsoft_sh*t", "/.*\\.js$/i", 'f', "includes/js/microsoft_sh*t", true),
				'file',
				true
			);
		}
	}
	protected function add_system_configs () {
		$Config         = Config::instance();
		$Route          = Route::instance();
		$User           = User::instance();
		$current_module = current_module();
		$this->config_internal(
			[
				'base_url'              => $Config->base_url(),
				'current_base_url'      => $Config->base_url().'/'.(admin_path() ? 'admin/' : '').$current_module,
				'public_key'            => Core::instance()->public_key,
				'module'                => $current_module,
				'in_admin'              => (int)admin_path(),
				'is_admin'              => (int)$User->admin(),
				'is_user'               => (int)$User->user(),
				'is_guest'              => (int)$User->guest(),
				'password_min_length'   => (int)$Config->core['password_min_length'],
				'password_min_strength' => (int)$Config->core['password_min_strength'],
				'debug'                 => (int)DEBUG,
				'route'                 => $Route->route,
				'route_path'            => $Route->path,
				'route_ids'             => $Route->ids
			],
			'cs',
			true
		);
		if ($User->guest()) {
			$this->config_internal(get_core_ml_text('rules'), 'cs.rules_text', true);
		}
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
			list($dependencies, $includes_map) = $this->includes_dependencies_and_map(admin_path());
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
			$includes = _substr($includes, strlen(DIR.'/'));
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
		$includes              = [
			'css'  => [],
			'js'   => [],
			'html' => []
		];
		$dependencies_includes = $includes;
		$current_module        = current_module();
		/**
		 * Current URL based on controller path (it better represents how page was rendered)
		 */
		$current_url = array_slice(Index::instance()->controller_path, 1);
		$current_url = (admin_path() ? "admin$separator" : '')."$current_module$separator".implode($separator, $current_url);
		/**
		 * Narrow the dependencies to current module only
		 */
		$dependencies = isset($dependencies[$current_module]) ? $dependencies[$current_module] : [];
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
		return
			$url_module !== Config::SYSTEM_MODULE &&
			in_array($url_module, $dependencies) &&
			(
				admin_path() || admin_path() == ($url_exploded[0] == 'admin')
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
	 * @param bool $with_disabled
	 *
	 * @return array
	 */
	protected function get_includes_list ($absolute = false, $with_disabled = false) {
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
			if (
				$module_data['active'] == Config\Module_Properties::UNINSTALLED ||
				(
					$module_data['active'] == Config\Module_Properties::DISABLED &&
					!$with_disabled
				)
			) {
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
				}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return array_reduce(array_filter($files, 'file_exists'), $callback, $content);
	}
	/**
	 * Get dependencies of components between each other (only that contains some HTML, JS and CSS files) and mapping HTML, JS and CSS files to URL paths
	 *
	 * @param bool $with_disabled
	 *
	 * @return array[] [$dependencies, $includes_map]
	 */
	protected function includes_dependencies_and_map ($with_disabled = false) {
		/**
		 * Get all includes
		 */
		$all_includes = $this->get_includes_list(true, $with_disabled);
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
			if (
				$module_data['active'] == Config\Module_Properties::UNINSTALLED ||
				(
					$module_data['active'] == Config\Module_Properties::DISABLED &&
					!$with_disabled
				)
			) {
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
				switch ($extension) {
					case 'css':
					case 'js':
					case 'html':
						$file                              = "$includes_dir/$extension/$file";
						$includes_map[$path][$extension][] = $file;
						$all_includes[$extension]          = array_diff($all_includes[$extension], [$file]);
						break;
					default:
						$file = rtrim($file, '*');
						/**
						 * Wildcard support, it is possible to specify just path prefix and all files with this prefix will be included
						 */
						foreach (['css', 'js', 'html'] as $extension) {
							$base_path   = "$includes_dir/$extension/$file";
							$found_files = get_files_list("$includes_dir/$extension", "/.*\\.$extension$/i", 'f', true, true, 'name', '!include') ?: [];
							foreach ($found_files as $f) {
								if (strpos($f, $base_path) === 0) {
									$includes_map[$path][$extension][] = $f;
									$all_includes[$extension]          = array_diff($all_includes[$extension], [$f]);
								}
							}
						}
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
