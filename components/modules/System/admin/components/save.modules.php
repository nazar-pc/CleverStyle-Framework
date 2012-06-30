<?php
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
$a = &$Index;
$rc			= &$Config->routing['current'];
$update		= false;
if (isset($_POST['update_modules_list'])) {
	/**
	 * List of currently presented modules in file system
	 */
	$modules_list	= array_fill_keys(
		$new_modules = get_list(MODULES, false, 'd'),
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
					$User->get_permission(null, $module),
					$User->get_permission(null, $module.'/admin')
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
			if ($Core->run_trigger(
				'admin/System/components/modules/install/process',
				[
					'name' => $_POST['module']
				]
			)) {
				$module_data['active'] = 0;
				if (isset($_POST['db']) && is_array($_POST['db'])) {
					$module_data['db'] = $_POST['db'];
				}
				if (isset($_POST['storage']) && is_array($_POST['storage'])) {
					$module_data['storage'] = $_POST['storage'];
				}
				$a->save('components');
				$permissions = [
					$_POST['module'] => ['index']
				];
				if (_file_exists(MODULES.DS.$_POST['module'].DS.'index.json')) {
					$structure = _json_decode(_file_get_contents(MODULES.DS.$_POST['module'].DS.'index.json'));
					foreach ($structure as $item => $part) {
						if (is_array($part)) {
							$permissions[$_POST['module']][] = $item;
							foreach ($part as $subpart) {
								$permissions[$_POST['module']][] = $item.'/'.$subpart;
							}
						} else {
							$permissions[$_POST['module']][] = $part;
						}
					}
					unset($structure, $item, $part, $subpart);
				}
				if (_file_exists(MODULES.DS.$_POST['module'].DS.'admin')) {
					$permissions[$_POST['module'].'/admin'] = ['index'];
					if (_file_exists(MODULES.DS.$_POST['module'].DS.'admin'.DS.'index.json')) {
						$structure = _json_decode(_file_get_contents(MODULES.DS.$_POST['module'].DS.'admin'.DS.'index.json'));
						foreach ($structure as $item => $part) {
							if (is_array($part)) {
								$permissions[$_POST['module'].'/admin'][] = $item;
								foreach ($part as $subpart) {
									$permissions[$_POST['module'].'/admin'][] = $item.'/'.$subpart;
								}
							} else {
								$permissions[$_POST['module'].'/admin'][] = $part;
							}
						}
						unset($structure, $item, $part, $subpart);
					}
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
			if ($Core->run_trigger(
				'admin/System/components/modules/uninstall/process',
				[
					'name' => $_POST['module']
				]
			)) {
				$module_data = ['active' => -1];
				$permissions_ids = array_merge(
					$User->get_permission(null, $_POST['module']),
					$User->get_permission(null, $_POST['module'].'/admin')
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
			if ($module_data['active'] == 1 && $_POST['module'] != 'System' && $_POST['module'] != $Config->core['default_module']) {
				if ($Core->run_trigger(
					'admin/System/components/modules/default_module/process',
					[
						'name' => $_POST['module']
					]
				)) {
					$Config->core['default_module'] = $_POST['module'];
					$a->save('core');
				}
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