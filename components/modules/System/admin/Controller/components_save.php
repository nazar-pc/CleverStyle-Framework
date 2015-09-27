<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Config,
	cs\Index,
	cs\Permission;

trait components_save {
	static function components_modules_save () {
		$Config     = Config::instance();
		$Permission = Permission::instance();
		$a          = Index::instance();
		if (isset($_POST['update_modules_list'])) {
			/**
			 * List of currently presented modules in file system
			 */
			$modules_list = array_fill_keys(
				$new_modules = get_files_list(MODULES, false, 'd'),
				[
					'active'  => -1,
					'db'      => [],
					'storage' => []
				]
			);
			/**
			 * Already known modules
			 */
			$modules     = &$Config->components['modules'];
			$old_modules = array_keys($modules);
			/**
			 * Deletion of undefined modules permissions
			 */
			if ($new_modules != $old_modules) {
				$permissions_ids = [];
				foreach ($old_modules as $module_name) {
					if (!isset($modules_list[$module_name])) {
						/** @noinspection SlowArrayOperationsInLoopInspection */
						$permissions_ids = array_merge(
							$permissions_ids,
							(array)$Permission->get(null, $module_name),
							(array)$Permission->get(null, "admin/$module_name"),
							(array)$Permission->get(null, "api/$module_name")
						);
					}
				}
				unset($old_modules, $module_name);
				if (!empty($permissions_ids)) {
					$Permission->del(
						array_column($permissions_ids, 'id')
					);
				}
				unset($permissions_ids);
			}
			unset($new_modules, $old_modules);
			$modules = array_merge($modules_list, array_intersect_key($modules, $modules_list));
			ksort($modules, SORT_STRING | SORT_FLAG_CASE);
			$a->save();
		}
	}
}
