<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page\Includes;
use
	cs\Config,
	cs\Event;

trait RequireJS {
	/**
	 * @return string[]
	 */
	protected function get_requirejs_paths () {
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
			$paths += $this->get_requirejs_paths_add_aliases(MODULES."/$module_name");
		}
		foreach ($Config->components['plugins'] as $plugin_name) {
			$paths += $this->get_requirejs_paths_add_aliases(PLUGINS."/$plugin_name");
		}
		foreach ($directories_to_browse as $dir) {
			foreach (get_files_list($dir, false, 'd', true) as $d) {
				$paths += $this->get_requirejs_paths_find_package($d);
			}
		}
		return $this->absolute_path_to_relative($paths);
	}
	/**
	 * @param string $dir
	 *
	 * @return string[]
	 */
	protected function get_requirejs_paths_add_aliases ($dir) {
		$paths = [];
		if (is_dir("$dir/includes/js")) {
			$name         = basename($dir);
			$paths[$name] = "$dir/includes/js";
			foreach ((array)@file_get_json("$dir/meta.json")['provide'] as $p) {
				if (strpos($p, '/') !== false) {
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
	protected function get_requirejs_paths_find_package ($dir) {
		$path = $this->get_requirejs_paths_find_package_bower($dir) ?: $this->get_requirejs_paths_find_package_npm($dir);
		return $path ? [basename($dir) => substr($path, 0, -3)] : [];
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
