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
	cs\Cache,
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Permission,
	cs\Session,
	h,
	cs\modules\System\Packages_manipulation;

trait components_save {
	/**
	 * Provides next events:
	 *  admin/System/components/modules/install/process
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/update_system/process/before
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/update_system/process/after
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/default
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/db/process
	 *  ['name'    => module_name]
	 *
	 *  admin/System/components/modules/storage/process
	 *  ['name'    => module_name]
	 */
	static function components_modules_save () {
		$Cache      = Cache::instance();
		$Config     = Config::instance();
		$Core       = Core::instance();
		$db         = DB::instance();
		$L          = Language::instance();
		$Page       = Page::instance();
		$Session    = Session::instance();
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
		} elseif (isset($_POST['mode'], $_POST['module'], $Config->components['modules'][$_POST['module']])) {
			$module_name = $_POST['module'];
			$module_data = &$Config->components['modules'][$module_name];
			switch ($_POST['mode']) {
				case 'install':
					if ($module_data['active'] != -1) {
						break;
					}
					unset($Cache->languages);
					if (!Event::instance()->fire(
						'admin/System/components/modules/install/process',
						[
							'name' => $module_name
						]
					)
					) {
						break;
					}
					$module_data['active'] = 0;
					$meta                  = file_exists(MODULES."/$module_name/meta.json") ? file_get_json(MODULES."/$module_name/meta.json") : null;
					if (isset($_POST['db'], $meta['db']) && is_array($_POST['db'])) {
						$module_data['db'] = $_POST['db'];
						time_limit_pause();
						foreach ($module_data['db'] as $db_name => $index) {
							if ($index == 0) {
								$db_type = $Core->db_type;
							} else {
								$db_type = $Config->db[$index]['type'];
							}
							$sql_file = MODULES."/$module_name/meta/install_db/$db_name/$db_type.sql";
							if (file_exists($sql_file)) {
								$db->$index()->q(
									explode(';', file_get_contents($sql_file))
								);
							}
						}
						unset($db_name, $index, $db_type, $sql_file);
						time_limit_pause(false);
					}
					if (isset($_POST['storage'], $meta['storage']) && is_array($_POST['storage'])) {
						$module_data['storage'] = $_POST['storage'];
					}
					if ($a->save()) {
						$Page->notice(
							"$L->module_installed_but_not_enabled ".
							h::{'a[is=cs-link-button]'}(
								$L->enable_module($module_name),
								[
									'href' => ''// TODO fix this link when installation migrate to frontend
								]
							)
						);
					}
					clean_pcache();
					unset($Cache->functionality);
					clean_classes_cache();
					break;
				case 'update_system':
					/**
					 * Temporary close site
					 */
					$site_mode = $Config->core['site_mode'];
					if ($site_mode) {
						$Config->core['site_mode'] = 0;
						$Config->save();
					}
					Event::instance()->fire(
						'admin/System/components/modules/update_system/process/before',
						[
							'name' => $module_name
						]
					);
					$module_dir  = MODULES.'/System';
					$old_version = file_get_json("$module_dir/meta.json")['version'];
					if (!Packages_manipulation::update_extract(DIR, TEMP.'/'.$Session->get_id().'_update_system.phar', DIR.'/core', $module_dir)) {
						$Page->warning($L->system_files_unpacking_error);
						break;
					}
					/**
					 * Updating of System
					 */
					if (isset(file_get_json("$module_dir/meta.json")['update_versions'])) {
						Packages_manipulation::update_php_sql(
							$module_dir,
							$old_version,
							$module_data['db']
						);
					}
					unset($old_version);
					/**
					 * Restore previous site mode
					 */
					if ($site_mode) {
						$Config->core['site_mode'] = 1;
					}
					$a->save();
					clean_pcache();
					clean_classes_cache();
					Event::instance()->fire(
						'admin/System/components/modules/update_system/process/after',
						[
							'name' => $module_name
						]
					);
					break;
				case 'db':
					/** @noinspection NotOptimalIfConditionsInspection */
					if (
						Event::instance()->fire(
							'admin/System/components/modules/db/process',
							[
								'name' => $module_name
							]
						) &&
						isset($_POST['db']) &&
						is_array($_POST['db']) &&
						count($Config->db) > 1
					) {
						$module_data['db'] = xap($_POST['db']);
						$a->save();
					}
					break;
				case 'storage':
					/** @noinspection NotOptimalIfConditionsInspection */
					if (
						Event::instance()->fire(
							'admin/System/components/modules/storage/process',
							[
								'name' => $module_name
							]
						) &&
						isset($_POST['storage']) &&
						is_array($_POST['storage']) &&
						count($Config->storage) > 1
					) {
						$module_data['storage'] = xap($_POST['storage']);
						$a->save();
					}
					break;
			}
		}
	}
}
