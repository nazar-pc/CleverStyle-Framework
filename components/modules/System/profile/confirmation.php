<?php
global $Config, $L, $User, $Page, $Mail;
if (isset($_COOKIE['confirm'])) {
	_setcookie('confirm', '');
	$Page->title($L->reg_success_title);
	$Page->notice($L->reg_success);
	return;
} elseif (!$User->is('guest')) {
	$Page->title($L->you_are_already_registered_title);
	$Page->warning($L->you_are_already_registered);
	return;
} elseif (!isset($Config->routing['current'][2])) {
	$Page->title($L->invalid_confirmation_code);
	$Page->warning($L->invalid_confirmation_code);
	return;
}
$result = $User->confirmation($Config->routing['current'][2]);
if ($result === false) {
	$Page->title($L->invalid_confirmation_code);
	$Page->warning($L->invalid_confirmation_code);
	return;
}
$body = $L->reg_success_mail_body(
	substr($result['email'], 0, strpos($result['email'], '@')),
	$Config->core['name'],
	$Config->core['url'].'/profile',
	$result['email'],
	$result['password']
);
if ($Mail->send_to(
	$result['email'],
	$L->reg_success_mail($Config->core['name']),
	$body
)) {
	_setcookie('confirm', 1);
	header('Location: '.$_SERVER['PHP_SELF']);
} else {
	$User->registration_cancel();
	$Page->title($L->sending_mail_error_title);
	$Page->warning($L->sending_mail_error);
}
?>