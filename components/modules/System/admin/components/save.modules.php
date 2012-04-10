<?php
global $Config, $Index;
$a = &$Index;
$rc			= &$Config->routing['current'];
$update		= false;
if (isset($_POST['update_modules_list'])) {
	$modules_list	= array_fill_keys(get_list(MODULES, false, 'd'), array('active' => -1, 'db' => [], 'storage' => []));
	$modules		= &$Config->components['modules'];
	$modules		= array_merge($modules_list, array_intersect_key($modules, $modules_list));
	ksort($modules);
	$a->save('components');
} elseif (isset($_POST['mode'], $_POST['module'], $Config->components['modules'][$_POST['module']])) {
	$module_data = &$Config->components['modules'][$_POST['module']];
	switch ($_POST['mode']) {
		case 'install':
			if ($a->run_trigger(
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
				global $User;
				$query = [];
				foreach ($permissions as $group => $list) {
					foreach ($list as $label) {
						$query[] = $User->db_prime()->sip($label).', '.$User->db_prime()->sip($group);
					}
				}
				unset($permissions, $group, $list, $label);
				$User->db_prime()->q(
					'INSERT INTO `[prefix]permissions` (`label`, `group`) VALUES ('.implode('), (', $query).')'
				);
			}
		break;
		case 'uninstall':
			if ($a->run_trigger(
				'admin/System/components/modules/uninstall/process',
				[
					'name' => $_POST['module']
				]
			)) {
				$module_data = ['active' => -1];
				global $User;
				$permissions_ids = $User->db_prime()->qfa('SELECT `id` FROM `[prefix]permissions`
					WHERE
						`group` = '.$User->db_prime()->sip($_POST['module']).' OR
						`group` = '.$User->db_prime()->sip($_POST['module'].'/admin')
				);
				if (!empty($permissions_ids)) {
					$permissions_ids = implode(',', $permissions_ids);
					$User->db_prime()->q([
						'DELETE FROM `[prefix]groups_permissions` WHERE `permission` IN ('.$permissions_ids.')',
						'DELETE FROM `[prefix]users_permissions` WHERE `permission` IN ('.$permissions_ids.')'
					]);
					global $Cache;
					unset($Cache->{'permissions_table'}, $Cache->{'users/permissions'}, $Cache->{'groups/permissions'});
				}
				$a->save('components');
			}
		break;
		case 'db':
			if ($a->run_trigger(
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
			if ($a->run_trigger(
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