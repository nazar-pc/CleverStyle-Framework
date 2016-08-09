--FILE--
<?php
namespace cs\User {
	function time ($time = 0) {
		static $stored_time;
		if (!isset($stored_time)) {
			$stored_time = \time();
		}
		if ($time) {
			$stored_time = $time;
		}
		return $stored_time;
	}
}
namespace cs {
	include __DIR__.'/../../bootstrap.php';
	$Config = Config::instance();
	$User   = User::instance();

	$result            = $User->registration('mpcr1@test.com', false, false);
	$password          = '1nc3lwvHBxaX9bYEKBzc';
	$password_prepared = hash('sha512', hash('sha512', $password).Core::instance()->public_key);

	var_dump('Password changing (raw)');
	var_dump($User->set_password($password, $result['id'], false));
	var_dump('Validation');
	var_dump($User->validate_password($password, $result['id'], false));
	var_dump($User->validate_password($password_prepared, $result['id'], true));

	var_dump('Password changing (prepared)');
	var_dump($User->set_password($password_prepared, $result['id'], true));
	var_dump('Validation');
	var_dump($User->validate_password($password, $result['id'], false));
	var_dump($User->validate_password($password_prepared, $result['id'], true));

	var_dump('Password changing (empty, raw)');
	var_dump($User->set_password('', $result['id']));

	var_dump('Password changing (empty, prepared)');
	var_dump($User->set_password(hash('sha512', hash('sha512', '').Core::instance()->public_key), $result['id'], true));

	var_dump('Password changing (guest)');
	var_dump($User->set_password($password, User::GUEST_ID));

	var_dump('Restore password');
	User\time(time());
	$key = $User->restore_password($result['id']);
	var_dump($key, $User->restore_password_confirmation($key));

	var_dump('Restore password (guest)');
	var_dump($User->restore_password(User::GUEST_ID));

	var_dump('Restore password timeout');
	$Config->core['registration_confirmation_time'] = 1;
	$key                                            = $User->restore_password($result['id']);
	User\time(time() + 86400 + 1);
	var_dump($key, $User->restore_password_confirmation($key));

	var_dump('Bad restore password key');
	var_dump($User->restore_password_confirmation(''));
	var_dump($User->restore_password_confirmation(md5(random_bytes(1000))));

	var_dump('Password validation failure');
	$User->set_password($password, $result['id']);
	var_dump($User->validate_password($password.'1', $result['id']));

	var_dump('Password rehashing');
	$hashed_password = password_hash($password_prepared, PASSWORD_DEFAULT, ['cost' => 5]);
	$User->set('password_hash', $hashed_password, $result['id']);
	Session::instance()->add($result['id']);
	var_dump($User->validate_password($password, $result['id']), $User->get('password_hash') == $hashed_password);
	var_dump($User->id === $result['id']);
}
?>
--EXPECTF--
string(23) "Password changing (raw)"
bool(true)
string(10) "Validation"
bool(true)
bool(true)
string(28) "Password changing (prepared)"
bool(true)
string(10) "Validation"
bool(true)
bool(true)
string(30) "Password changing (empty, raw)"
bool(false)
string(35) "Password changing (empty, prepared)"
bool(false)
string(25) "Password changing (guest)"
bool(false)
string(16) "Restore password"
string(32) "%s"
array(2) {
  ["id"]=>
  int(16)
  ["password"]=>
  string(4) "%s"
}
string(24) "Restore password (guest)"
bool(false)
string(24) "Restore password timeout"
string(32) "%s"
bool(false)
string(24) "Bad restore password key"
bool(false)
bool(false)
string(27) "Password validation failure"
bool(false)
string(18) "Password rehashing"
bool(true)
bool(false)
bool(true)
