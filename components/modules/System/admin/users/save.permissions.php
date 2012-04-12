<?php
if (!isset($_POST['mode'])) {
	return;
}
global $Index, $User, $Cache;
switch ($_POST['mode']) {
	case 'add':
		$Index->save(
			$User->add_permission($_POST['permission']['group'], $_POST['permission']['label'])
		);
	break;
	case 'edit':
		$Index->save(
			$User->set_permission($_POST['permission']['id'], $_POST['permission']['group'], $_POST['permission']['label'])
		);
	break;
	case 'delete':
		$Index->save(
			$User->del_permission($_POST['id'])
		);
	break;
}