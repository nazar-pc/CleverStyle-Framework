﻿<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $Page, $User, $L, $Mail;
/**
 * If AJAX request from local referer, user is guest - send request for password restore, otherwise - show error
 */
if (
	!$Config->server['referer']['local'] ||
	!$Config->server['ajax'] ||
	!isset($_POST['email']) ||
	!$User->guest()
) {
	sleep(1);
	define('ERROR_CODE', 403);
	return;
} elseif (!$_POST['email']) {
	define('ERROR_CODE', 400);
	$Page->error($L->please_type_your_email);
	return;
} elseif (!($id = $User->get_id(mb_strtolower($_POST['email'])))) {
	define('ERROR_CODE', 400);
	$Page->error($L->user_with_such_login_email_not_found);
	return;
}
if (
	($key = $User->restore_password($id)) &&
	$Mail->send_to(
		$User->get('email', $id),
		$L->restore_password_confirmation_mail(get_core_ml_text('name')),
		$L->restore_password_confirmation_mail_body(
			$User->username($id),
			get_core_ml_text('name'),
			$Config->core_url().'/profile/restore_password_confirmation/'.$key,
			$L->time($Config->core['registration_confirmation_time'], 'd')
		)
	)
) {
	$Page->json('OK');
} else {
	define('ERROR_CODE', 500);
	$Page->error($L->restore_password_server_error);
}