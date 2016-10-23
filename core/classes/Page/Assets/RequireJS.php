<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page\Assets;
use
	cs\Config,
	cs\Event;

class RequireJS {
	/**
	 * @return string[]
	 */
	public static function get_config () {
		$Config                = Config::instance();
		$paths                 = [];
		$packages              = [];
		$directories_to_browse = [
			DIR.'/bower_components',
			DIR.'/node_modules'
		];
		Event::instance()->fire(
			'System/Page/requirejs',
			[
				'paths'                 => &$paths,
				'packages'              => &$packages,
				'directories_to_browse' => &$directories_to_browse
			]
		);
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			$paths += static::add_aliases(MODULES."/$module_name");
		}
		$allowed_extensions = $Config->core['cache_compress_js_css'] ? ['min.js', 'js'] : ['js'];
		foreach ($directories_to_browse as $dir) {
			foreach (get_files_list($dir, false, 'd', true) as $d) {
				$packages[] = static::find_package_main_path($d, $allowed_extensions);
			}
		}
		return [
			'paths'    => _substr($paths, strlen(DIR)),
			'packages' => array_values(array_filter($packages))
		];
	}
	/**
	 * @param string $dir
	 *
	 * @return string[]
	 */
	protected static function add_aliases ($dir) {
		$paths = [];
		if (is_dir("$dir/assets/js")) {
			$name         = basename($dir);
			$paths[$name] = "$dir/assets/js";
			foreach ((array)@file_get_json("$dir/meta.json")['provide'] as $p) {
				if (strpos($p, '/') === false) {
					$paths[$p] = $paths[$name];
				}
			}
		}
		return $paths;
	}
	/**
	 * @param string   $dir
	 * @param string[] $allowed_extensions
	 *
	 * @return string[]
	 */
	protected static function find_package_main_path ($dir, $allowed_extensions) {
		$path = static::find_package_bower($dir, $allowed_extensions) ?: static::find_package_npm($dir, $allowed_extensions);
		if (!$path) {
			return [];
		}
		return [
			'name'     => basename($dir),
			'main'     => substr($path, strlen($dir) + 1, -3),
			'location' => substr($dir, strlen(DIR))
		];
	}
	/**
	 * @param string   $dir
	 * @param string[] $allowed_extensions
	 *
	 * @return false|string
	 */
	protected static function find_package_bower ($dir, $allowed_extensions) {
		$bower = @file_get_json("$dir/bower.json");
		foreach (@(array)$bower['main'] as $main) {
			if (preg_match('/\.js$/', $main)) {
				$main = substr($main, 0, -3);
				$main = file_exists_with_extension("$dir/$main", $allowed_extensions);
				if ($main) {
					return $main;
				}
			}
		}
		return false;
	}
	/**
	 * @param string   $dir
	 * @param string[] $allowed_extensions
	 *
	 * @return false|string
	 */
	protected static function find_package_npm ($dir, $allowed_extensions) {
		$package = @file_get_json("$dir/package.json");
		// If we have browser-specific declaration - use it
		/** @noinspection NestedTernaryOperatorInspection */
		$main = @$package['browser'] ?: (@$package['jspm']['main'] ?: @$package['main']);
		if (preg_match('/\.js$/', $main)) {
			$main = substr($main, 0, -3);
		}
		if ($main) {
			return file_exists_with_extension("$dir/$main", $allowed_extensions) ?: file_exists_with_extension("$dir/dist/$main", $allowed_extensions);
		}
		return false;
	}
}
