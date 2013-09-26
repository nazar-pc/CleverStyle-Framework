<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$User	= User::instance();
/**
 * If AJAX request from local referer, user is registered - change password, otherwise - show error
 */
if (
	!$Config->server['referer']['local'] ||
	!$Config->server['ajax'] ||
	!isset($_POST['verify_hash'], $_POST['new_password']) ||
	!$User->user()
) {
	sleep(1);
	error_code(403);
	return;
} elseif (!$_POST['new_password']) {
	error_code(400);
	$Page->error($L->please_type_new_password);
	return;
} elseif (hash('sha224', $User->password_hash.$User->get_session()) != $_POST['verify_hash']) {
	error_code(400);
	$Page->error($L->wrong_current_password);
	return;
} elseif (($new_password = xor_string($_POST['new_password'], $User->password_hash)) == $User->password_hash) {
	error_code(400);
	$Page->error($L->current_new_password_equal);
	return;
}
if ($new_password == hash('sha512', hash('sha512', '').Core::instance()->public_key)) {
	error_code(400);
	$Page->error($L->please_type_new_password);
	return;
}
$id	= $User->id;
if ($User->set('password_hash', $new_password)) {
	$User->add_session($id);
	$Page->json('OK');
} else {
	error_code(400);
	$Page->error($L->change_password_server_error);
}