<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Page;
use
	cs\Core,
	cs\Config,
	cs\Index,
	cs\Language,
	cs\Trigger,
	cs\User,
	h;

/**
 * Open Graph functionality for <i>cs\Page</i> class
 *
 * @property string $Title
 * @property string $Description
 * @property string $canonical_url
 * @property string $Head
 * @property string $post_Body
 * @property string $theme
 */
trait Includes {
	protected	$core_html		= [0 => [], 1 => ''];
	protected	$core_js		= [0 => [], 1 => []];
	protected	$core_css		= [0 => [], 1 => []];
	protected	$core_config	= '';
	protected	$html			= [0 => [], 1 => ''];
	protected	$js				= [0 => [], 1 => []];
	protected	$css			= [0 => [], 1 => []];
	protected	$config			= '';
	/**
	 * Base name is used as prefix when creating CSS/JS/HTML cache files in order to avoid collisions when having several themes and languages
	 * @var string
	 */
	protected	$pcache_basename;
	/**
	 * Including of Web Components
	 *
	 * @param string|string[]	$add	Path to including file, or code
	 * @param string			$mode	Can be <b>file</b> or <b>code</b>
	 *
	 * @return \cs\Page
	 */
	function html ($add, $mode = 'file') {
		return $this->html_internal($add, $mode);
	}
	/**
	 * @param string|string[]	$add
	 * @param string			$mode
	 * @param bool				$core
	 *
	 * @return \cs\Page
	 */
	protected function html_internal ($add, $mode = 'file', $core = false) {
		if (!$add) {
			return $this;
		}
		if (is_array($add)) {
			foreach ($add as $script) {
				if ($script) {
					$this->html_internal($script, $mode, $core);
				}
			}
			return $this;
		}
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
		return $this;
	}
	/**
	 * Including of JavaScript
	 *
	 * @param string|string[]	$add	Path to including file, or code
	 * @param string			$mode	Can be <b>file</b> or <b>code</b>
	 *
	 * @return \cs\Page
	 */
	function js ($add, $mode = 'file') {
		return $this->js_internal($add, $mode);
	}
	/**
	 * @param string|string[]	$add
	 * @param string			$mode
	 * @param bool				$core
	 *
	 * @return \cs\Page
	 */
	protected function js_internal ($add, $mode = 'file', $core = false) {
		if (!$add) {
			return $this;
		}
		if (is_array($add)) {
			foreach ($add as $script) {
				if ($script) {
					$this->js_internal($script, $mode, $core);
				}
			}
			return $this;
		}
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
		return $this;
	}
	/**
	 * Including of CSS
	 *
	 * @param string|string[]	$add	Path to including file, or code
	 * @param string			$mode	Can be <b>file</b> or <b>code</b>
	 *
	 * @return \cs\Page
	 */
	function css ($add, $mode = 'file') {
		return $this->css_internal($add, $mode);
	}
	/**
	 * @param string|string[]	$add
	 * @param string			$mode
	 * @param bool				$core
	 *
	 * @return \cs\Page
	 */
	protected function css_internal ($add, $mode = 'file', $core = false) {
		if (!$add) {
			return $this;
		}
		if (is_array($add)) {
			foreach ($add as $style) {
				if ($style) {
					$this->css_internal($style, $mode, $core);
				}
			}
			return $this;
		}
		if ($core) {
			if ($mode == 'file') {
				$this->core_css[0][]	= h::link([
					'href'				=> $add,
					'rel'				=> 'stylesheet',
					'shim-shadowdom'	=> ''
				]);
			} elseif ($mode == 'code') {
				$this->core_css[1][]	 = $add."\n";
			}
		} else {
			if ($mode == 'file') {
				$this->css[0][]			= h::link([
					'href'				=> $add,
					'rel'				=> 'stylesheet',
					'shim-shadowdom'	=> ''
				]);
			} elseif ($mode == 'code') {
				$this->css[1][]			 = $add."\n";
			}
		}
		return $this;
	}
	/**
	 * Add config on page to make it available on frontend
	 *
	 * @param mixed		$config_structure	Any scalar type or array
	 * @param string	$target				Target is property of `window` object where config will be inserted as value, nested properties like `cs.sub.prop`
	 * 										are supported and all nested properties are created on demand. It is recommended to use sub-properties of `cs`
	 *
	 * @return \cs\Page
	 */
	function config ($config_structure, $target) {
		return $this->config_internal($config_structure, $target);
	}
	/**
	 * @param mixed		$config_structure
	 * @param string	$target
	 * @param bool		$core
	 *
	 * @return \cs\Page
	 */
	protected function config_internal ($config_structure, $target, $core = false) {
		$config	= h::template(
			'<!--'.str_replace('-', '- ', _json_encode($config_structure)).'-->',
			[
				'target'	=> $target,
				'class'		=> 'cs-config',
				'level'		=> 0
			]
		);
		if ($core) {
			$this->core_config	.= "$config\n";
		} else {
			$this->config		.= "$config\n";
		}
		return $this;
	}
	/**
	 * Getting of CSS and JavaScript includes
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
		$this->pcache_basename	= "_{$this->theme}_".Language::instance()->clang;
		$Index					= Index::instance();
		$User					= User::instance();
		/**
		 * Some JS code required by system
		 */
		$this->config_internal([
			'base_url'			=> $Config->base_url(),
			'current_base_url'	=> $Config->base_url().'/'.($Index->in_admin() ? 'admin/' : '').current_module(),
			'public_key'		=> Core::instance()->public_key,
			'module'			=> current_module(),
			'in_admin'			=> (int)$Index->in_admin(),
			'is_admin'			=> (int)$User->admin(),
			'is_user'			=> (int)$User->user(),
			'is_guest'			=> (int)$User->guest(),
			'debug'				=> (int)DEBUG,
			'cookie_prefix'		=> $Config->core['cookie_prefix'],
			'cookie_domain'		=> $Config->core['cookie_domain'][$Config->server['mirror_index']],
			'cookie_path'		=> $Config->core['cookie_path'][$Config->server['mirror_index']],
			'protocol'			=> $Config->server['protocol'],
			'route'				=> $Config->route,
			'route_path'		=> $Index->route_path,
			'route_ids'			=> $Index->route_ids
		], 'cs', true);
		if ($User->guest()) {
			$this->config_internal(get_core_ml_text('rules'), 'cs.rules_text', true);
		}
		/**
		 * If CSS and JavaScript compression enabled
		 */
		if ($Config->core['cache_compress_js_css'] && !admin_path()) {
			$this->add_includes_on_page_with_compression($Config);
		} else {
			/**
			 * Language translation is added explicitly only when compression is disabled, otherwise it will be in compressed JS file
			 */
			/**
			 * @var \cs\Page $this
			 */
			$this->config_internal(Language::instance(), 'cs.Language', true);
			$this->add_includes_on_page_without_compression($Config);
		}
		$this->add_includes_on_page_manually_added($Config);
		return $this;
	}
	protected function add_includes_on_page_with_compression ($Config) {
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
	}
	protected function add_includes_on_page_without_compression ($Config) {
		if ($Config) {
			$this->includes_dependencies_and_map($dependencies, $includes_map, admin_path());
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
						$dependencies_includes['css']  = array_merge($dependencies_includes['css'], @$local_includes['css'] ?: []);
						$dependencies_includes['js']   = array_merge($dependencies_includes['js'], @$local_includes['js'] ?: []);
						$dependencies_includes['html'] = array_merge($dependencies_includes['html'], @$local_includes['html'] ?: []);
					} else {
						$includes['css']  = array_merge($includes['css'], @$local_includes['css'] ?: []);
						$includes['js']   = array_merge($includes['js'], @$local_includes['js'] ?: []);
						$includes['html'] = array_merge($includes['html'], @$local_includes['html'] ?: []);
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
	protected function add_includes_on_page_manually_added ($Config) {
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
		$this->Head			.=
			$this->core_config.
			$this->config.
			$this->core_css[0].$this->css[0].
			h::style($this->core_css[1].$this->css[1] ?: false).
			h::script($this->core_js[1].$this->js[1] ?: false);
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
	}
	/**
	 * Getting of JavaScript and CSS files list to be included
	 *
	 * @param bool		$absolute		If <i>true</i> - absolute paths to files will be returned
	 * @param bool		$with_disabled
	 *
	 * @return array
	 */
	protected function get_includes_list ($absolute = false, $with_disabled = false) {
		$theme_dir		= THEMES."/$this->theme";
		$theme_pdir		= "themes/$this->theme";
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
		 * Get includes of system and theme
		 */
		$includes	= [
			'css' => array_merge(
				$get_files(DIR.'/includes/css', $absolute ? true : 'includes/css'),
				$get_files("$theme_dir/css", $absolute ? true : "$theme_pdir/css")
			),
			'js' => array_merge(
				$get_files(DIR.'/includes/js', $absolute ? true : 'includes/js'),
				$get_files("$theme_dir/js", $absolute ? true : "$theme_pdir/js")
			),
			'html' => array_merge(
				$get_files(DIR.'/includes/html', $absolute ? true : 'includes/html'),
				$get_files("$theme_dir/html", $absolute ? true : "$theme_pdir/html")
			)
		];
		unset($theme_dir, $theme_pdir);
		$Config		= Config::instance();
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if (
				$module_data['active'] == -1 ||
				(
					$module_data['active'] == 0 &&
					!$with_disabled
				)
			) {
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
	 * @return \cs\Page
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
	 * @param bool	$with_disabled
	 */
	protected function includes_dependencies_and_map (&$dependencies, &$includes_map, $with_disabled = false) {
		/**
		 * Get all includes
		 */
		$all_includes			= $this->get_includes_list(true, $with_disabled);
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
			if (
				$module_data['active'] == -1 ||
				(
					$module_data['active'] == 0 &&
					!$with_disabled
				)
			) {
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
						/**
						 * If provides sub-functionality of other component (Blog/post_patch) - inverse "providing" to "dependency"
						 */
						if (strpos($p, '/') !== false) {
							$p                  = explode('/', $p)[0];
							$dependencies[$p][] = $module_name;
						} else {
							$dependencies_aliases[$p]	= $module_name;
						}
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
						/**
						 * If provides sub-functionality of other component (Blog/post_patch) - inverse "providing" to "dependency"
						 */
						if (strpos($p, '/') !== false) {
							$p                  = explode('/', $p)[0];
							$dependencies[$p][] = $plugin_name;
						} else {
							$dependencies_aliases[$p]	= $plugin_name;
						}
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
		$cache_hash		= [];
		$destination	= Config::instance()->core['vulcanization'] ? false : PCACHE;
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
						$destination
					);
				} else {
					$files_content .= file_get_contents($file).";\n";
				}
			}
			if ($filename_prefix == '' && $extension == 'js') {
				$files_content	= "window.cs={};cs.Language="._json_encode(Language::instance()).";$files_content";
			}
			file_put_contents(PCACHE."/$filename_prefix$this->pcache_basename.$extension", gzencode($files_content, 9), LOCK_EX | FILE_BINARY);
			$cache_hash[$extension]	= substr(md5($files_content), 0, 5);
		}
		return $cache_hash;
	}
}
