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
trait modules {
	static function admin_modules_get () {
		$Config       = Config::instance();
		$modules_list = [];
		foreach ($Config->components['modules'] as $module_name => &$module_data) {
			$module = [
				'active'                => $module_data['active'],
				'name'                  => $module_name,
				'is_default'            => $module_name == $Config->core['default_module'],
				'can_be_set_as_default' =>
					$module_data['active'] == 1 &&
					$module_name != $Config->core['default_module'] &&
					file_exists_with_extension(MODULES."/$module_name/index", ['php', 'html', 'json']),
				'db_settings'           => !$Config->core['simple_admin_mode'] && @$module_data['db'] && count($Config->db) > 1,
				'storage_settings'      => !$Config->core['simple_admin_mode'] && @$module_data['storage'] && count($Config->storage) > 1,
				'administration'        =>
					$module_data['active'] != -1 &&
					file_exists_with_extension(MODULES."/$module_name/admin/index", ['php', 'json'])
			];
			/**
			 * Check if API available
			 */
			if (is_dir(MODULES."/$module_name/api")) {
				$module['api'] = [];
				$file          = file_exists_with_extension(MODULES."/$module_name/api/readme", ['txt', 'html']);
				if ($file) {
					$module['api'] = [
						'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
						'content' => file_get_contents($file)
					];
				}
				unset($file);
			}
			/**
			 * Check if readme available
			 */
			$file = file_exists_with_extension(MODULES."/$module_name/readme", ['txt', 'html']);
			if ($file) {
				$module['readme'] = [
					'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
					'content' => file_get_contents($file)
				];
			}
			unset($file);
			/**
			 * Check if license available
			 */
			$file = file_exists_with_extension(MODULES."/$module_name/license", ['txt', 'html']);
			if ($file) {
				$module['license'] = [
					'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
					'content' => file_get_contents($file)
				];
			}
			unset($file);
			if (file_exists(MODULES."/$module_name/meta.json")) {
				$module['meta'] = file_get_json(MODULES."/$module_name/meta.json");
			}
			$modules_list[] = $module;
		}
		unset($module_name, $module_data, $module);
		Page::instance()->json($modules_list);
	}
}
