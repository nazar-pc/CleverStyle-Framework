<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $Page, $User, $L;
/**
 * If AJAX request from local referer, user is guest, registration is allowed - process registration, otherwise - show error
 */
if (
	!$Config->server['referer']['local'] ||
	!$Config->server['ajax'] ||
	!isset($_POST['email'])
) {
	sleep(1);
	return;
} elseif (!$User->guest()) {
	$Page->content('reload');
	return;
} elseif (!$Config->core['allow_user_registration']) {
	$Page->content($L->registration_prohibited);
	return;
} elseif (empty($_POST['email'])) {
	$Page->content($L->please_type_your_email);
	sleep(1);
	return;
} elseif (file_exists(MFOLDER.'/registration/'.str_replace('/', '', $_POST['email']).'.php')) {
	//TODO foreign login systems processing
	return;
}
$result		= $User->registration($_POST['email']);
if ($result === false) {
	$Page->content($L->please_type_correct_email);
	sleep(1);
	return;
} elseif ($result == 'error') {
	$Page->content($L->reg_server_error);
	return;
} elseif ($result == 'exists') {
	$Page->content($L->reg_error_exists);
	return;
}
$confirm	= $result['reg_key'] !== true;
if ($confirm) {
	$body	= $L->reg_need_confirmation_mail_body(
		strstr($_POST['email'], '@', true),
		get_core_ml_text('name'),
		$Config->core['base_url'].'/profile/registration_confirmation/'.$result['reg_key'],
		$L->time($Config->core['registration_confirmation_time'], 'd')
	);
} else {
	$body	= $L->reg_success_mail_body(
		strstr($_POST['email'], '@', true),
		get_core_ml_text('name'),
		$Config->core['base_url'].'/profile/'.$User->get('login', $result['id']),
		$User->get('login', $result['id']),
		$result['password']
	);
}
global $Mail;
if ($Mail->send_to(
	$_POST['email'],
	$L->{$confirm ? 'reg_need_confirmation_mail' : 'reg_success_mail'}(get_core_ml_text('name')),
	$body
)) {
	$Page->content($confirm ? 'reg_confirmation' : 'reg_success');
} else {
	$User->registration_cancel();
	$Page->content($L->sending_reg_mail_error);
}