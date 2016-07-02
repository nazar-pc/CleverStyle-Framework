<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page\Includes;
use
	cs\Config,
	cs\Event;

trait Collecting {
	/**
	 * Getting of HTML, JS and CSS files list to be included
	 *
	 * @param Config $Config
	 *
	 * @return string[][]
	 */
	protected function get_includes_list ($Config) {
		$includes = [];
		/**
		 * Get includes of system and theme
		 */
		$this->fill_includes(DIR.'/includes', $includes);
		$this->fill_includes(THEMES."/$this->theme", $includes);
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			$this->fill_includes(MODULES."/$module_name/includes", $includes);
		}
		return [
			'html' => array_merge(...$includes['html']),
			'js'   => array_merge(...$includes['js']),
			'css'  => array_merge(...$includes['css'])
		];
	}
	/**
	 * @param string     $base_dir
	 * @param string[][] $includes
	 */
	protected function fill_includes ($base_dir, &$includes) {
		$includes['html'][] = $this->fill_includes_internal($base_dir, 'html');
		$includes['js'][]   = $this->fill_includes_internal($base_dir, 'js');
		$includes['css'][]  = $this->fill_includes_internal($base_dir, 'css');
	}
	/**
	 * @param string $base_dir
	 * @param string $ext
	 *
	 * @return array
	 */
	protected function fill_includes_internal ($base_dir, $ext) {
		return get_files_list("$base_dir/$ext", "/.*\\.$ext\$/i", 'f', true, true, 'name', '!include') ?: [];
	}
	/**
	 * Get dependencies of components between each other (only that contains some HTML, JS and CSS files) and mapping HTML, JS and CSS files to URL paths
	 *
	 * @param Config $Config
	 *
	 * @return array[] [$dependencies, $includes_map]
	 */
	protected function get_includes_dependencies_and_map ($Config) {
		/**
		 * Get all includes
		 */
		$all_includes = $this->get_includes_list($Config);
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
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			$this->process_meta(MODULES."/$module_name", $dependencies, $functionalities, $module_data['active'] != Config\Module_Properties::ENABLED);
			$this->process_map(MODULES."/$module_name", $includes_map, $all_includes);
		}
		unset($module_name, $module_data);
		/**
		 * For consistency
		 */
		$includes_map['System'] = $all_includes;
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
	 * @param string $base_dir
	 * @param array  $dependencies
	 * @param array  $functionalities
	 * @param bool   $skip_functionalities
	 */
	protected function process_meta ($base_dir, &$dependencies, &$functionalities, $skip_functionalities = false) {
		if (!file_exists("$base_dir/meta.json")) {
			return;
		}
		$meta = file_get_json("$base_dir/meta.json");
		$meta += [
			'require'  => [],
			'optional' => [],
			'provide'  => []
		];
		$package = $meta['package'];
		foreach ((array)$meta['require'] as $r) {
			/**
			 * Get only name of package or functionality
			 */
			$r                        = preg_split('/[=<>]/', $r, 2)[0];
			$dependencies[$package][] = $r;
		}
		foreach ((array)$meta['optional'] as $o) {
			/**
			 * Get only name of package or functionality
			 */
			$o                        = preg_split('/[=<>]/', $o, 2)[0];
			$dependencies[$package][] = $o;
		}
		if ($skip_functionalities) {
			return;
		}
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
	}
	/**
	 * Process map structure, fill includes map and remove files from list of all includes (remaining files will be included on all pages)
	 *
	 * @param string $base_dir
	 * @param array  $includes_map
	 * @param array  $all_includes
	 */
	protected function process_map ($base_dir, &$includes_map, &$all_includes) {
		if (!file_exists("$base_dir/includes/map.json")) {
			return;
		}
		$this->process_map_internal(file_get_json("$base_dir/includes/map.json"), "$base_dir/includes", $includes_map, $all_includes);
	}
	/**
	 * Process map structure, fill includes map and remove files from list of all includes (remaining files will be included on all pages)
	 *
	 * @param array  $map
	 * @param string $includes_dir
	 * @param array  $includes_map
	 * @param array  $all_includes
	 */
	protected function process_map_internal ($map, $includes_dir, &$includes_map, &$all_includes) {
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
					$this->process_map_internal([$path => $found_files], $includes_dir, $includes_map, $all_includes);
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
			unset($dependency);
		}
		unset($depends_on);
		/**
		 * Third round, build dependencies tree using references to corresponding recursive dependencies
		 */
		foreach ($dependencies as &$depends_on) {
			foreach ($depends_on as &$dependency) {
				if ($dependency != 'System' && isset($dependencies[$dependency])) {
					$dependency = [&$dependencies[$dependency], $dependency];
				}
			}
			unset($dependency);
		}
		unset($depends_on);
		$dependencies = array_map([$this, 'array_flatten'], $dependencies);
		return array_map('array_unique', $dependencies);
	}
	/**
	 * Convert array of arbitrary nested structure into flat array
	 *
	 * @param array $array
	 *
	 * @return string[]
	 */
	protected function array_flatten ($array) {
		foreach ($array as &$a) {
			if (is_array($a)) {
				$a = $this->array_flatten($a);
			}
		}
		return array_merge(..._array($array));
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
