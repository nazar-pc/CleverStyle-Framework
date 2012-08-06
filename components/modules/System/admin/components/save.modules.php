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
 *  admin/System/components/modules/install/process<br>
 *  ['name'	=> <i>module_name</i>]
 */
global $Config, $Index, $User, $Core;
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
					(array)$User->get_permission(null, $module.'/admin'),
					(array)$User->get_permission(null, $module.'/api')
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
			if ($Core->run_trigger(
				'admin/System/components/modules/install/process',
				[
					'name' => $_POST['module']
				]
			)) {
				$module_data['active'] = 0;
				if (isset($_POST['db']) && is_array($_POST['db'])) {
					$module_data['db'] = $_POST['db'];
					if (file_exists(MODULES.'/'.$_POST['module'].'/meta/install')) {
						global $db;
						foreach ($module_data['db'] as $db_name => $db_id) {
							if (file_exists(MODULES.'/'.$_POST['module'].'/meta/install/'.$Config->db[$db_id]['type'].'/'.$db_name.'.sql')) {
								$db->$db_id()->q(
									explode(';', file_get_contents(MODULES.'/'.$_POST['module'].'/meta/install/'.$Config->db[$db_id]['type'].'/'.$db_name.'.sql'))
								);
							}
						}
						unset($db_name, $db_id);
					}
				}
				if (isset($_POST['storage']) && is_array($_POST['storage'])) {
					$module_data['storage'] = $_POST['storage'];
				}
				$a->save('components');
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
					$permissions[$_POST['module'].'/admin'] = ['index'];
					if (file_exists(MODULES.'/'.$_POST['module'].'/admin/index.json')) {
						$structure = _json_decode(file_get_contents(MODULES.'/'.$_POST['module'].'/admin/index.json'));
						foreach ($structure as $item => $part) {
							$permissions[$_POST['module'].'/admin'][] = is_array($part) ? $item : $part;
						}
						unset($structure, $item, $part);
					}
					$permissions[$_POST['module'].'/admin']	= array_unique($permissions[$_POST['module'].'/admin']);
				}
				/**
				 * Adding module API permissions
				 */
				if (file_exists(MODULES.'/'.$_POST['module'].'/api')) {
					$permissions[$_POST['module'].'/api'] = ['index'];
					if (file_exists(MODULES.'/'.$_POST['module'].'/api/index.json')) {
						$structure = _json_decode(file_get_contents(MODULES.'/'.$_POST['module'].'/api/index.json'));
						foreach ($structure as $item => $part) {
							$permissions[$_POST['module'].'/api'][] = is_array($part) ? $item : $part;
						}
						unset($structure, $item, $part);
					}
					$permissions[$_POST['module'].'/api']	= array_unique($permissions[$_POST['module'].'/api']);
				}
				foreach ($permissions as $group => $list) {
					foreach ($list as $label) {
						$User->add_permission($group, $label);
					}
				}
				unset($permissions, $group, $list, $label);
			}
		break;
		case 'uninstall':
			if ($module_data['active'] != -1 || $_POST['module'] == 'System' || $_POST['module'] == $Config->core['default_module']) {
				break;
			}
			if ($Core->run_trigger(
				'admin/System/components/modules/uninstall/process',
				[
					'name' => $_POST['module']
				]
			)) {
				if (file_exists(MODULES.'/'.$_POST['module'].'/meta/uninstall')) {
					global $db;
					foreach ($module_data['db'] as $db_name => $db_id) {
						if (file_exists(MODULES.'/'.$_POST['module'].'/meta/uninstall/'.$Config->db[$db_id]['type'].'/'.$db_name.'.sql')) {
							$db->$db_id()->q(
								explode(';', file_get_contents(MODULES.'/'.$_POST['module'].'/meta/uninstall/'.$Config->db[$db_id]['type'].'/'.$db_name.'.sql'))
							);
						}
					}
					unset($db_name, $db_id);
				}
				$module_data = ['active' => -1];
				$permissions_ids = array_merge(
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
				$a->save('components');
			}
		break;
		case 'default_module':
			if ($module_data['active'] != 1 || $_POST['module'] == $Config->core['default_module']) {
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
				'admin/System/components/modules/install/process',
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