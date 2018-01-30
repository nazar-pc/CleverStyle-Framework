<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs\Page\Assets;
use
	cs\Config,
	cs\Event;

class Collecting {
	/**
	 * Get dependencies of components between each other (only that contains some HTML, JS and CSS files) and mapping HTML, JS and CSS files to URL paths
	 *
	 * @param Config $Config
	 * @param string $theme
	 *
	 * @return array[] [$dependencies, $assets_map]
	 */
	public static function get_assets_dependencies_and_map ($Config, $theme) {
		$installed_modules = array_filter(
			$Config->components['modules'],
			function ($module_data) {
				return $module_data['active'] != Config\Module_Properties::UNINSTALLED;
			}
		);
		/**
		 * Get all assets
		 */
		$all_assets = static::get_assets_list(array_keys($installed_modules), $theme);
		$assets_map = [];
		/**
		 * Array [package => [list of packages it depends on]]
		 */
		$dependencies    = [];
		$functionalities = [];
		/**
		 * According to components's maps some files should be included only on specific pages.
		 * Here we read this rules, and remove from whole assets list such items, that should be included only on specific pages.
		 * Also collect dependencies.
		 */
		foreach ($installed_modules as $module => $module_data) {
			$not_enabled = $module_data['active'] != Config\Module_Properties::ENABLED;
			static::process_meta(MODULES."/$module", $dependencies, $functionalities, $assets_map, $all_assets, $not_enabled);
		}
		unset($module, $module_data);
		/**
		 * For consistency
		 */
		$assets_map['System'] = $all_assets;
		Event::instance()->fire(
			'System/Page/assets_dependencies_and_map',
			[
				'dependencies' => &$dependencies,
				'assets_map'   => &$assets_map
			]
		);
		$assets_map   = static::webcomponents_support_filter($assets_map, $theme, (bool)$Config->core['disable_webcomponents']);
		$dependencies = static::normalize_dependencies($dependencies, $functionalities);
		$assets_map   = static::clean_assets_arrays_without_files($dependencies, $assets_map);
		$assets_map   = array_map(
			function ($assets) {
				return array_map('array_values', $assets);
			},
			$assets_map
		);
		$dependencies = array_filter(array_map('array_values', $dependencies));
		return [$dependencies, $assets_map];
	}
	/**
	 * Getting of HTML, JS and CSS files list to be included
	 *
	 * @param string[] $modules
	 * @param string   $theme
	 *
	 * @return string[][]
	 */
	protected static function get_assets_list ($modules, $theme) {
		$assets = [];
		/**
		 * Get assets of system and theme
		 */
		static::fill_assets(DIR.'/assets', $assets);
		static::fill_assets(THEMES."/$theme", $assets);
		foreach ($modules as $module) {
			static::fill_assets(MODULES."/$module/assets", $assets);
		}
		return [
			'html' => array_merge(...$assets['html']),
			'js'   => array_merge(...$assets['js']),
			'css'  => array_merge(...$assets['css'])
		];
	}
	/**
	 * @param string     $base_dir
	 * @param string[][] $assets
	 */
	protected static function fill_assets ($base_dir, &$assets) {
		$assets['html'][] = static::fill_assets_internal($base_dir, 'html');
		$assets['js'][]   = static::fill_assets_internal($base_dir, 'js');
		$assets['css'][]  = static::fill_assets_internal($base_dir, 'css');
	}
	/**
	 * @param string $base_dir
	 * @param string $ext
	 *
	 * @return array
	 */
	protected static function fill_assets_internal ($base_dir, $ext) {
		return get_files_list("$base_dir/$ext", "/.*\\.$ext\$/i", 'f', true, true, 'name', '!include') ?: [];
	}
	/**
	 * Process meta information and corresponding entries to dependencies and functionalities, fill assets map and remove files from list of all assets
	 * (remaining files will be included on all pages)
	 *
	 * @param string $base_dir
	 * @param array  $dependencies
	 * @param array  $functionalities
	 * @param array  $assets_map
	 * @param array  $all_assets
	 * @param bool   $skip_functionalities
	 */
	protected static function process_meta ($base_dir, &$dependencies, &$functionalities, &$assets_map, &$all_assets, $skip_functionalities = false) {
		if (!file_exists("$base_dir/meta.json")) {
			return;
		}
		$meta = file_get_json("$base_dir/meta.json");
		$meta += [
			'assets'   => [],
			'require'  => [],
			'optional' => [],
			'provide'  => []
		];
		$package    = $meta['package'];
		$depends_on = array_merge((array)$meta['require'], (array)$meta['optional']);
		foreach ($depends_on as $d) {
			/**
			 * Get only name of package or functionality
			 */
			$dependencies[$package][] = preg_split('/[=<>]/', $d, 2)[0];
		}
		if (!$skip_functionalities) {
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
		static::process_assets_map($meta['assets'], "$base_dir/assets", $assets_map, $all_assets);
	}
	/**
	 * Process map structure, fill assets map and remove files from list of all assets (remaining files will be included on all pages)
	 *
	 * @param array  $map
	 * @param string $assets_dir
	 * @param array  $assets_map
	 * @param array  $all_assets
	 */
	protected static function process_assets_map ($map, $assets_dir, &$assets_map, &$all_assets) {
		foreach ($map as $path => $files) {
			foreach ((array)$files as $file) {
				$extension = file_extension($file);
				if (in_array($extension, ['css', 'js', 'html'])) {
					$file                            = "$assets_dir/$extension/$file";
					$assets_map[$path][$extension][] = $file;
					$all_assets[$extension]          = array_diff($all_assets[$extension], [$file]);
				} else {
					$file = rtrim($file, '*');
					/**
					 * Wildcard support, it is possible to specify just path prefix and all files with this prefix will be included
					 */
					$found_files = array_filter(
						get_files_list($assets_dir, '/.*\.(css|js|html)$/i', 'f', '', true, 'name', '!include') ?: [],
						function ($f) use ($file) {
							// We need only files with specified mask and only those located in directory that corresponds to file's extension
							return preg_match("#^(css|js|html)/$file.*\\1$#i", $f);
						}
					);
					// Drop first level directory
					$found_files = _preg_replace('#^[^/]+/(.*)#', '$1', $found_files);
					static::process_assets_map([$path => $found_files], $assets_dir, $assets_map, $all_assets);
				}
			}
		}
	}
	/**
	 * Replace functionalities by real packages names, take into account recursive dependencies
	 *
	 * @param array[] $dependencies
	 * @param array   $functionalities
	 *
	 * @return array
	 */
	protected static function normalize_dependencies ($dependencies, $functionalities) {
		/**
		 * First of all remove packages without any dependencies
		 */
		$dependencies = array_filter($dependencies);
		/**
		 * First round, process aliases among dependencies
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
		 * Second round, build dependencies tree using references to corresponding recursive dependencies
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
		$dependencies = array_map([static::class, 'array_flatten'], $dependencies);
		return array_map('array_unique', $dependencies);
	}
	/**
	 * Convert array of arbitrary nested structure into flat array
	 *
	 * @param array $array
	 *
	 * @return string[]
	 */
	protected static function array_flatten ($array) {
		foreach ($array as &$a) {
			if (is_array($a)) {
				$a = static::array_flatten($a);
			}
		}
		return array_merge(..._array($array));
	}
	/**
	 * If system is configured to not use Web Components - all HTML imports and Polymer-related JS code will be removed from assets map
	 *
	 * @param array[] $assets_map
	 * @param string  $theme
	 * @param bool    $disable_webcomponents
	 *
	 * @return array[]
	 */
	protected static function webcomponents_support_filter ($assets_map, $theme, $disable_webcomponents) {
		if ($theme != Config::SYSTEM_THEME && $disable_webcomponents) {
			foreach ($assets_map as &$assets) {
				unset($assets['html']);
			}
			unset($assets);
			$prefix    = DIR.'/assets/js/Polymer';
			$system_js = &$assets_map[Config::SYSTEM_MODULE]['js'];
			foreach ($system_js as $index => $file) {
				if (strpos($file, $prefix) === 0) {
					unset($system_js[$index]);
				}
			}
			$system_js = array_values($system_js);
		}
		return $assets_map;
	}
	/**
	 * Assets array is composed from dependencies and sometimes dependencies doesn't have any files, so we'll clean that
	 *
	 * @param array $dependencies
	 * @param array $assets_map
	 *
	 * @return array
	 */
	protected static function clean_assets_arrays_without_files ($dependencies, $assets_map) {
		foreach ($dependencies as &$depends_on) {
			foreach ($depends_on as $index => &$dependency) {
				if (!isset($assets_map[$dependency])) {
					unset($depends_on[$index]);
				}
			}
			unset($dependency);
		}
		return $assets_map;
	}
}
