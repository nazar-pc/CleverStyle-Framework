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
	public static function get_paths () {
		$Config                = Config::instance();
		$paths                 = [];
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
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			$paths += static::add_aliases(MODULES."/$module_name");
		}
		foreach ($directories_to_browse as $dir) {
			foreach (get_files_list($dir, false, 'd', true) as $d) {
				$paths += static::find_package($d);
			}
		}
		return _substr($paths, strlen(DIR));
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
	 * @param string $dir
	 *
	 * @return string[]
	 */
	protected static function find_package ($dir) {
		$path = static::find_package_bower($dir) ?: static::find_package_npm($dir);
		return $path ? [basename($dir) => substr($path, 0, -3)] : [];
	}
	/**
	 * @param string $dir
	 *
	 * @return false|string
	 */
	protected static function find_package_bower ($dir) {
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
		return false;
	}
	/**
	 * @param string $dir
	 *
	 * @return false|string
	 */
	protected static function find_package_npm ($dir) {
		$package = @file_get_json("$dir/package.json");
		// If we have browser-specific declaration - use it
		/** @noinspection NestedTernaryOperatorInspection */
		$main = @$package['browser'] ?: (@$package['jspm']['main'] ?: @$package['main']);
		if (preg_match('/\.js$/', $main)) {
			$main = substr($main, 0, -3);
		}
		if ($main) {
			// There is a chance that minified file is present
			return file_exists_with_extension("$dir/$main", ['min.js', 'js']) ?: file_exists_with_extension("$dir/dist/$main", ['min.js', 'js']);
		}
		return false;
	}
}
