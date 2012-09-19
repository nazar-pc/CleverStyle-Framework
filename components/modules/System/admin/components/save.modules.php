<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  admin/System/components/modules/install/process<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/uninstall/process<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/default_module/process<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/db/process<br>
 *  ['name'	=> <i>module_name</i>]<br>
 *
 *  admin/System/components/modules/storage/process<br>
 *  ['name'	=> <i>module_name</i>]
 */
global $Config, $Index, $User, $Core, $Cache;
$a			= $Index;
$rc			= $Config->routing['current'];
if (isset($_POST['update_modules_list'])) {
	/**
	 * List of currently presented modules in file system
	 */
	$modules_list	= array_fill_keys(
		$new_modules = get_files_list(MODULES, false, 'd'),
		[
			'active'	=> -1,
			'db'		=> [],
			'storage'	=> []
		]
	);
	/**
	 * Already known modules
	 */
	$modules		= &$Config->components['modules'];
	$old_modules	= array_keys($modules);
	/**
	 * Deletion of undefined modules permissions
	 */
	if ($new_modules != $old_modules) {
		$permissions_ids = [];
		foreach ($old_modules as $module) {
			if (!isset($modules_list[$module])) {
				$permissions_ids = array_merge(
					$permissions_ids,
					(array)$User->get_permission(null, $module),
					(array)$User->get_permission(null, 'admin/'.$module),
					(array)$User->get_permission(null, 'api/'.$module)
				);
			}
		}
		unset($old_modules, $module);
		if (!empty($permissions_ids)) {
			foreach ($permissions_ids as &$id) {
				$id = $id['id'];
			}
			unset($id);
			$User->del_permission($permissions_ids);
		}
		unset($permissions_ids);
	}
	unset($new_modules, $old_modules);
	$modules			= array_merge($modules_list, array_intersect_key($modules, $modules_list));
	ksort($modules);
	$a->save('components');
} elseif (isset($_POST['mode'], $_POST['module'], $Config->components['modules'][$_POST['module']])) {
	$module_data = &$Config->components['modules'][$_POST['module']];
	switch ($_POST['mode']) {
		case 'install':
			if ($module_data['active'] != -1) {
				break;
			}
			unset($Cache->languages);
			if (!$Core->run_trigger(
				'admin/System/components/modules/install/process',
				[
					'name' => $_POST['module']
				]
			)) {
				break;
			}
			$module_data['active'] = 0;
			if (isset($_POST['db']) && is_array($_POST['db']) && file_exists(MODULES.'/'.$_POST['module'].'/meta/db.json')) {
				$module_data['db'] = $_POST['db'];
				$db_json = _json_decode(file_get_contents(MODULES.'/'.$_POST['module'].'/meta/db.json'));
				global $db;
				time_limit_pause();
				foreach ($db_json as $database) {
					if ($module_data['db'][$database] == 0) {
						$db_type	= $Core->config('db_type');
					} else {
						$db_type	= $Config->db[$module_data['db'][$database]]['type'];
					}
					$sql_file	= MODULES.'/'.$_POST['module'].'/meta/install_db/'.$database.'/'.$db_type.'.sql';
					if (file_exists($sql_file)) {
						$db->{$module_data['db'][$database]}()->q(
							explode(';', file_get_contents($sql_file))
						);
					}
				}
				unset($db_json, $database, $db_type, $sql_file);
				time_limit_pause(false);
			}
			if (isset($_POST['storage']) && is_array($_POST['storage'])) {
				$module_data['storage'] = $_POST['storage'];
			}
			$permissions = [
				$_POST['module'] => ['index']
			];
			/**
			 * Adding module permissions
			 */
			if (file_exists(MODULES.'/'.$_POST['module'].'/index.json')) {
				$structure = _json_decode(file_get_contents(MODULES.'/'.$_POST['module'].'/index.json'));
				foreach ($structure as $item => $part) {
					$permissions[$_POST['module']][] = is_array($part) ? $item : $part;
				}
				unset($structure, $item, $part);
			}
			/**
			 * Adding module admin permissions
			 */
			if (file_exists(MODULES.'/'.$_POST['module'].'/admin')) {
				$permissions['admin/'.$_POST['module']] = ['index'];
				if (file_exists(MODULES.'/'.$_POST['module'].'/admin/index.json')) {
					$structure = _json_decode(file_get_contents(MODULES.'/'.$_POST['module'].'/admin/index.json'));
					foreach ($structure as $item => $part) {
						$permissions['admin/'.$_POST['module']][] = is_array($part) ? $item : $part;
					}
					unset($structure, $item, $part);
				}
				$permissions['admin/'.$_POST['module']]	= array_unique($permissions['admin/'.$_POST['module']]);
			}
			/**
			 * Adding module API permissions
			 */
			if (file_exists(MODULES.'/'.$_POST['module'].'/api')) {
				$permissions['api/'.$_POST['module']] = ['index'];
				if (file_exists(MODULES.'/'.$_POST['module'].'/api/index.json')) {
					$structure = _json_decode(file_get_contents(MODULES.'/'.$_POST['module'].'/api/index.json'));
					foreach ($structure as $item => $part) {
						$permissions['api/'.$_POST['module']][] = is_array($part) ? $item : $part;
					}
					unset($structure, $item, $part);
				}
				$permissions['api/'.$_POST['module']]	= array_unique($permissions['api/'.$_POST['module']]);
			}
			foreach ($permissions as $group => $list) {
				foreach ($list as $label) {
					$User->add_permission($group, $label);
				}
			}
			unset($permissions, $group, $list, $label);
			$a->save('components');
		break;
		case 'uninstall':
			if ($module_data['active'] == -1 || $_POST['module'] == 'System' || $_POST['module'] == $Config->core['default_module']) {
				break;
			}
			unset($Cache->languages);
			if (!$Core->run_trigger(
				'admin/System/components/modules/uninstall/process',
				[
					'name' => $_POST['module']
				]
			)) {
				break;
			}
			$module_data['active']	= -1;
			$Core->run_trigger(
				'admin/System/components/modules/disable',
				[
					'name'	=> $_POST['module']
				]
			);
			$Config->save('components');
			if (isset($module_data['db']) && file_exists(MODULES.'/'.$_POST['module'].'/meta/db.json')) {
				$db_json = _json_decode(file_get_contents(MODULES.'/'.$_POST['module'].'/meta/db.json'));
				global $db;
				time_limit_pause();
				foreach ($db_json as $database) {
					if ($module_data['db'][$database] == 0) {
						$db_type	= $Core->config('db_type');
					} else {
						$db_type	= $Config->db[$module_data['db'][$database]]['type'];
					}
					$sql_file	= MODULES.'/'.$_POST['module'].'/meta/uninstall_db/'.$database.'/'.$db_type.'.sql';
					if (file_exists($sql_file)) {
						$db->{$module_data['db'][$database]}()->q(
							explode(';', file_get_contents($sql_file))
						);
					}
				}
				unset($db_json, $database, $db_type, $sql_file);
				time_limit_pause(false);
			}
			$permissions_ids		= array_merge(
				$User->get_permission(null, $_POST['module']),
				$User->get_permission(null, $_POST['module'].'/admin'),
				$User->get_permission(null, $_POST['module'].'/api')
			);
			if (!empty($permissions_ids)) {
				foreach ($permissions_ids as &$id) {
					$id = $id['id'];
				}
				$User->del_permission($permissions_ids);
			}
			$module_data			= ['active' => -1];
			$a->save('components');
		break;
		case 'default_module':
			if (
				$module_data['active'] != 1 ||
				$_POST['module'] == $Config->core['default_module'] ||
				!(
					file_exists(MODULES.'/'.$_POST['module'].'/index.php') || file_exists(MODULES.'/'.$_POST['module'].'/index.html')
				)
			) {
				break;
			}
			if ($Core->run_trigger(
				'admin/System/components/modules/default_module/process',
				[
					'name' => $_POST['module']
				]
			)) {
				$Config->core['default_module'] = $_POST['module'];
				$a->save('core');
			}
		break;
		case 'db':
			if ($Core->run_trigger(
				'admin/System/components/modules/db/process',
				[
					'name' => $_POST['module']
				]
			)) {
				if (isset($_POST['db']) && is_array($_POST['db']) && count($Config->db) > 1) {
					$module_data['db'] = xap($_POST['db']);
					$a->save('components');
				}
			}
		break;
		case 'storage':
			if ($Core->run_trigger(
				'admin/System/components/modules/storage/process',
				[
					'name' => $_POST['module']
				]
			)) {
				if(isset($_POST['storage']) && is_array($_POST['storage']) && count($Config->storage) > 1) {
					$module_data['storage'] = xap($_POST['storage']);
					$a->save('components');
				}
			}
		break;
	}
}