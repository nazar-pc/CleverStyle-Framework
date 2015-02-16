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
if (_getcookie('reg_confirm')) {
	_setcookie('reg_confirm', '');
	$Page->title($L->reg_success_title);
	$Page->success($L->reg_success);
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
$result = $User->registration_confirmation($Config->route[2]);
if ($result === false) {
	$Page->title($L->invalid_confirmation_code);
	$Page->warning($L->invalid_confirmation_code);
	return;
}
$body = $L->reg_success_mail_body(
	strstr($result['email'], '@', true),
	get_core_ml_text('name'),
	$Config->core_url().'/profile/settings',
	$User->get('login', $result['id']),
	$result['password']
);
if (Mail::instance()->send_to(
	$result['email'],
	$L->reg_success_mail(get_core_ml_text('name')),
	$body
)) {
	_setcookie('reg_confirm', 1);
	_header("Location: {$Config->base_url()}/System/profile/registration_confirmation");
} else {
	$User->registration_cancel();
	$Page->title($L->sending_reg_mail_error_title);
	$Page->warning($L->sending_reg_mail_error);
}
