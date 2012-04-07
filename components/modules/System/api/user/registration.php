<?php
global $Config, $Page, $User, $L;
//Если AJAX запрос от локального реферала, пользователь гость и количество попыток входа допустимо,
//пользователь активен, и не блокиирован - выполняем операцию аутентификации, иначе выдаем ошибку
if (!$Config->server['referer']['local'] || !$Config->server['ajax'] || !isset($_POST['email'])) {
	sleep(1);
	return;
} elseif (!$User->is('guest')) {
	$Page->content('reload');
	return;
} elseif (empty($_POST['email'])) {
	$Page->content($L->please_type_your_email);
	sleep(1);
	return;
} elseif (file_exists(MFOLDER.DS.'registration'.DS.str_replace('/', '', $_POST['email']).'.php')) {
	//TODO foreign login systems processing
	return;
}
$result = $User->registration($_POST['email']);
if ($result === false) {
	$Page->content($L->please_type_correct_email);
	sleep(1);
	return;
} elseif ($result == 'error') {
	$Page->content($L->reg_error_server);
	return;
} elseif ($result == 'exists') {
	$Page->content($L->reg_error_exists);
	return;
}
global $Mail;
$confirm = $Config->core['require_registration_confirmation'];
if ($confirm) {
	$body = $L->reg_need_confirmation_mail_body(
		substr($_POST['email'], 0, strpos($_POST['email'], '@')),
		$Config->core['name'],
		$Config->core['url'].'/profile/confirmation/'.$result['reg_key'],
		$L->time($Config->core['registration_confirmation_time'], 'd')
	);
} else {
	$body = $L->reg_success_mail_body(
		substr($_POST['email'], 0, strpos($_POST['email'], '@')),
		$Config->core['name'],
		$Config->core['url'].'/profile',
		$_POST['email'],
		$result['password']
	);
}
if ($Mail->send_to(
	$_POST['email'],
	$L->{$confirm ? 'reg_need_confirmation_mail' : 'reg_success_mail'}($Config->core['name']),
	$body
)) {
	$Page->content($confirm ? 'reg_confirmation' : 'reg_success');
} else {
	$User->registration_cancel();
	$Page->content($L->sending_mail_error);
}
?>