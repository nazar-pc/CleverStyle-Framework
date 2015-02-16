<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$User	= User::instance();
if (_getcookie('restore_password_confirm')) {
	_setcookie('restore_password_confirm', '');
	$Page->title($L->restore_password_success_title);
	$Page->success($L->restore_password_success);
	return;
} elseif (!$User->guest()) {
	$Page->title($L->you_are_already_registered_title);
	$Page->warning($L->you_are_already_registered);
	return;
} elseif (!isset($Config->route[2])) {
	$Page->title($L->invalid_confirmation_code);
	$Page->warning($L->invalid_confirmation_code);
	return;
}
$result = $User->restore_password_confirmation($Config->route[2]);
if ($result === false) {
	$Page->title($L->invalid_confirmation_code);
	$Page->warning($L->invalid_confirmation_code);
	return;
}
if (Mail::instance()->send_to(
	$User->get('email', $result['id']),
	$L->restore_password_success_mail(get_core_ml_text('name')),
	$L->restore_password_success_mail_body(
		$User->username($result['id']),
		get_core_ml_text('name'),
		$Config->core_url().'/profile/settings',
		$User->get('login', $result['id']),
		$result['password']
	)
)) {
	_setcookie('restore_password_confirm', 1);
	_header("Location: {$Config->base_url()}/System/profile/restore_password_confirmation");
} else {
	$Page->title($L->sending_reg_mail_error_title);
	$Page->warning($L->sending_reg_mail_error);
}
