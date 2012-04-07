<?php
if (!isset($_POST['mode'])) {
	return;
}
global $Config, $Page, $Index, $User, $L;
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'edit':
			$group_data = &$_POST['group'];
			$columns = array(
				'id',
				'title',
				'description',
				'data'
			);
			foreach ($group_data as $item => &$value) {
				if (in_array($item, $columns) && $item != 'data') {
					$value = xap($value, false);
				}
			}
			unset($item, $value, $columns);
			//TODO use save_group_data here
			$User->__finish();
			$Index->save(true);
		break;
		case 'delete':
			$Index->save(
				$User->delete_group($_POST['id'])
			);
		break;
		case 'permissions':
			$Index->save(
				$User->set_group_permissions($_POST['permission'], $_POST['id'])
			);
	}
}