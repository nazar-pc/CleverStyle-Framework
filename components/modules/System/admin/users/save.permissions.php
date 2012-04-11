<?php
if (!isset($_POST['mode'])) {
	return;
}
global $Index, $User, $Cache;
$u_db = $User->db_prime();
switch ($_POST['mode']) {
	case 'edit':
		$permission			= &$_POST['permission'];
		$permission['id']	= (int)$permission['id'];
		$permission			= xap($permission);
		$u_db->q('UPDATE `[prefix]permissions`
			SET
				`label` = '.$u_db->sip($permission['label']).',
				`group` = '.$u_db->sip($permission['group']).'
			WHERE
				`id` = '.$permission['id'].'
			LIMIT 1');
		$User->del_permission_table();
		$Index->save(true);
	break;
	case 'delete':
		$id = (int)$_POST['id'];
		$u_db->q([
			'DELETE FROM `[prefix]permissions` WHERE `id` = '.$id.' LIMIT 1',
			'DELETE FROM `[prefix]groups_permissions` WHERE `permission` = '.$id,
			'DELETE FROM `[prefix]users_permissions` WHERE `permission` = '.$id
		]);
		$User->del_permission_table();
		unset($Cache->{'users/permissions'}, $Cache->{'groups/permissions'});
		$Index->save(true);
		break;
}