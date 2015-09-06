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
	cs\Page;
trait plugins {
	static function admin_plugins_get () {
		$Config       = Config::instance();
		$plugins      = get_files_list(PLUGINS, false, 'd');
		$plugins_list = [];
		foreach ($plugins as $plugin_name) {
			$plugin = [
				'active' => in_array($plugin_name, $Config->components['plugins']),
				'name'   => $plugin_name
			];
			/**
			 * Check if readme available
			 */
			$file = file_exists_with_extension(PLUGINS."/$plugin_name/readme", ['txt', 'html']);
			if ($file) {
				$plugin['readme'] = [
					'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
					'content' => file_get_contents($file)
				];
			}
			unset($file);
			/**
			 * Check if license available
			 */
			$file = file_exists_with_extension(PLUGINS."/$plugin_name/license", ['txt', 'html']);
			if ($file) {
				$plugin['license'] = [
					'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
					'content' => file_get_contents($file)
				];
			}
			unset($file);
			if (file_exists(PLUGINS."/$plugin_name/meta.json")) {
				$plugin['meta'] = file_get_json(PLUGINS."/$plugin_name/meta.json");
			}
			$plugins_list[] = $plugin;
		}
		unset($plugin_name, $plugin);
		Page::instance()->json($plugins_list);
	}
}
