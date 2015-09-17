<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Cache,
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Page,
	cs\modules\System\Packages_manipulation;
trait plugins {
	/**
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_plugins_get ($route_ids, $route_path) {
		if (isset($route_path[3])) {
			switch ($route_path[3]) {
				/**
				 * Get dependent packages for plugin
				 */
				case 'dependent_packages':
					static::get_dependent_packages_for_plugin($route_path[2]);
					break;
				default:
					throw new ExitException(400);
			}
			return;
		}
		/**
		 * Get array of plugins in extended form
		 */
		static::get_plugins_list();
	}
	/**
	 * @param string $plugin
	 *
	 * @throws ExitException
	 */
	protected static function get_dependent_packages_for_plugin ($plugin) {
		if (!in_array($plugin, Config::instance()->components['plugins'])) {
			throw new ExitException(404);
		}
		$meta_file = PLUGINS."/$plugin/meta.json";
		Page::instance()->json(
			file_exists($meta_file) ? Packages_manipulation::get_dependent_packages(file_get_json($meta_file)) : []
		);
	}
	protected static function get_plugins_list () {
		$Config       = Config::instance();
		$plugins      = get_files_list(PLUGINS, false, 'd');
		$plugins_list = [];
		foreach ($plugins as $plugin_name) {
			$plugin = [
				'active' => (int)in_array($plugin_name, $Config->components['plugins']),
				'name'   => $plugin_name
			];
			/**
			 * Check if readme available
			 */
			static::check_plugin_feature_availability($plugin, 'readme');
			/**
			 * Check if license available
			 */
			static::check_plugin_feature_availability($plugin, 'license');
			if (file_exists(PLUGINS."/$plugin_name/meta.json")) {
				$plugin['meta'] = file_get_json(PLUGINS."/$plugin_name/meta.json");
			}
			$plugins_list[] = $plugin;
		}
		unset($plugin_name, $plugin);
		Page::instance()->json($plugins_list);
	}
	/**
	 * @param array  $plugin
	 * @param string $feature
	 */
	protected static function check_plugin_feature_availability (&$plugin, $feature) {
		/**
		 * Check if feature available
		 */
		$file = file_exists_with_extension(PLUGINS."/$plugin[name]/$feature", ['txt', 'html']);
		if ($file) {
			$plugin[$feature] = [
				'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
				'content' => file_get_contents($file)
			];
		}
	}
	/**
	 * Disable plugin
	 *
	 * Provides next events:
	 *  admin/System/components/plugins/disable
	 *  ['name'    => plugin_name]
	 *
	 * @param int[]    $route_ids
	 * @param string[] $route_path
	 *
	 * @throws ExitException
	 */
	static function admin_plugins_disable ($route_ids, $route_path) {
		if (!isset($route_path[2])) {
			throw new ExitException(400);
		}
		$plugin       = $route_path[2];
		$Config       = Config::instance();
		$plugin_index = array_search($plugin, $Config->components['plugins'], true);
		if ($plugin_index !== false) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire(
			'admin/System/components/plugins/disable',
			[
				'name' => $plugin
			]
		)
		) {
			throw new ExitException(500);
		}
		unset($Config->components['plugins'][$plugin_index]);
		if (!$Config->save()) {
			throw new ExitException(500);
		}
		clean_pcache();
		unset(Cache::instance()->functionality);
		clean_classes_cache();
	}
	/**
	 * Delete plugin completely
	 *
	 * @throws ExitException
	 */
	static function admin_plugins_delete () {
		if (!isset($route_path[2])) {
			throw new ExitException(400);
		}
		$plugin = $route_path[2];
		$Config = Config::instance();
		if (
			!is_dir(PLUGINS."/$plugin") ||
			in_array($plugin, $Config->components['plugins'])
		) {
			throw new ExitException(400);
		}
		if (!rmdir_recursive(PLUGINS."/$plugin")) {
			throw new ExitException(500);
		}
	}
}
