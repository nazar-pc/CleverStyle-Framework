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
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$User	= User::instance();
if (!isset($_POST['email'])) {
	error_code(400);
	return;
} elseif (!$User->guest()) {
	error_code(403);
	return;
} elseif (!$_POST['email']) {
	error_code(400);
	$Page->error($L->please_type_your_email);
	return;
} elseif (!($id = $User->get_id(mb_strtolower($_POST['email'])))) {
	error_code(400);
	$Page->error($L->user_with_such_login_email_not_found);
	return;
}
if (
	($key = $User->restore_password($id)) &&
	Mail::instance()->send_to(
		$User->get('email', $id),
		$L->restore_password_confirmation_mail(get_core_ml_text('name')),
		$L->restore_password_confirmation_mail_body(
			$User->username($id),
			get_core_ml_text('name'),
			$Config->core_url()."/profile/restore_password_confirmation/$key",
			$L->time($Config->core['registration_confirmation_time'], 'd')
		)
	)
) {
	$Page->json('OK');
} else {
	error_code(500);
	$Page->error($L->restore_password_server_error);
}
