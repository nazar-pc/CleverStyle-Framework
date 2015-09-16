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
	cs\Config,
	cs\ExitException,
	cs\Page;
trait plugins {
	/**
	 * Get array of plugins in extended form
	 */
	static function admin_plugins_get () {
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
		$file = file_exists_with_extension(MODULES."/$plugin[name]/$feature", ['txt', 'html']);
		if ($file) {
			$plugin[$feature] = [
				'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
				'content' => file_get_contents($file)
			];
		}
	}
	/**
	 * Delete plugin completely
	 *
	 * @throws ExitException
	 */
	static function admin_plugins_delete () {
		if (!isset($_POST['plugin'])) {
			throw new ExitException(400);
		}
		$plugin = $_POST['plugin'];
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
