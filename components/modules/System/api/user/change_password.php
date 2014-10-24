<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$L		= Language::instance();
$Page	= Page::instance();
$User	= User::instance();
if (!isset($_POST['current_password'], $_POST['new_password'])) {
	error_code(400);
	return;
}
if (!$User->user()) {
	error_code(403);
	return;
} elseif (!$_POST['new_password']) {
	error_code(400);
	$Page->error($L->please_type_new_password);
	return;
} elseif (!$User->validate_password($_POST['current_password'], $User->id, true)) {
	error_code(400);
	$Page->error($L->wrong_current_password);
	return;
}
$id	= $User->id;
if ($User->set_password($_POST['new_password'], $id, true)) {
	$User->add_session($id);
	$Page->json('OK');
} else {
	error_code(400);
	$Page->error($L->change_password_server_error);
}
