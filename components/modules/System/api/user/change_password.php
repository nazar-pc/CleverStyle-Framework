<?php
global $Config, $Page, $User, $L;
//Если AJAX запрос от локального реферала, пользователь зарегистрирован - меняем пароль, иначе выдаем ошибку
if (
	!$Config->server['referer']['local'] ||
	!$Config->server['ajax'] ||
	!isset($_POST['verify_hash'], $_POST['new_password'])
) {
	sleep(1);
	return;
} elseif (!$User->is('user')) {
	return;
} elseif (!$_POST['new_password']) {
	$Page->content($L->please_type_new_password);
	return;
} elseif (hash('sha224', $User->password_hash.$User->get_session()) != $_POST['verify_hash']) {
	$Page->content($L->wrong_current_password);
	return;
} elseif (($new_password = xor_string($_POST['new_password'], $User->password_hash)) == $User->password_hash) {
	$Page->content($L->current_new_password_equal);
	return;
}
if ($User->set('password_hash', $new_password)) {
	$Page->content('OK');
} else {
	$Page->content($L->change_password_error_server);
}