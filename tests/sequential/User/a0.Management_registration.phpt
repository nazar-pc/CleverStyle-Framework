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
	$Event  = Event::instance();
	$User   = User::instance();
	$Event
		->on(
			'System/User/registration/before',
			function ($data) {
				var_dump('System/User/registration/before fired with', $data);
			}
		)
		->on(
			'System/User/registration/after',
			function ($data) {
				var_dump('System/User/registration/after fired with', $data);
			}
		);

	var_dump('Register new user without confirmation necessary and without automatic sign-in');
	var_dump($User->registration('mr1@test.com', false, false), $User->id);

	$Config->core['auto_sign_in_after_registration'] = true;
	var_dump('Register new user without confirmation necessary and with automatic sign-in according to system configuration (true)');
	var_dump($User->registration('mr2@test.com', false, true), $User->id, $User->email);
	Session::instance()->del();

	$Config->core['auto_sign_in_after_registration'] = false;
	var_dump('Register new user without confirmation necessary and with automatic sign-in according to system configuration (false)');
	var_dump($User->registration('mr3@test.com', false, true), $User->id);

	$Config->core['require_registration_confirmation'] = true;
	var_dump('Register new user with system confirmation settings (true) and without automatic sign-in');
	var_dump($User->registration('mr4@test.com', true, false), $User->id);

	$Config->core['require_registration_confirmation'] = false;
	var_dump('Register new user with system confirmation settings (false) and without automatic sign-in');
	var_dump($User->registration('mr5@test.com', true, false), $User->id);

	var_dump('Cancel registration in System/User/registration/before event');
	$Event->once(
		'System/User/registration/before',
		function () {
			return false;
		}
	);
	var_dump($User->registration('mr6@test.com', false, true), $User->id);

	var_dump('Cancel registration in System/User/registration/after event');
	$Event->once(
		'System/User/registration/after',
		function () {
			return false;
		}
	);
	var_dump($User->registration('mr6@test.com', false, true), $User->id);

	var_dump('Incorrect email');
	var_dump($User->registration('1 2 3', false, true), $User->id);

	var_dump('Existing email');
	var_dump($User->registration('mr1@test.com', false, true), $User->id);

	var_dump('Delete unconfirmed users');
	$Config->core['require_registration_confirmation'] = true;
	$Config->core['registration_confirmation_time'] = 1;
	$result = $User->registration('mr7@test.com', true, true);
	var_dump($User->get('email', $result['id']));
	User\time(time() + 86400 + 1);
	$User->registration('mr8@test.com', true, true);
	var_dump($User->get('email', $result['id']));
}
?>
--EXPECTF--
string(78) "Register new user without confirmation necessary and without automatic sign-in"
string(42) "System/User/registration/before fired with"
array(1) {
  ["email"]=>
  string(12) "mr1@test.com"
}
string(41) "System/User/registration/after fired with"
array(1) {
  ["id"]=>
  int(4)
}
array(3) {
  ["reg_key"]=>
  bool(true)
  ["password"]=>
  string(4) "%s"
  ["id"]=>
  int(4)
}
int(1)
string(116) "Register new user without confirmation necessary and with automatic sign-in according to system configuration (true)"
string(42) "System/User/registration/before fired with"
array(1) {
  ["email"]=>
  string(12) "mr2@test.com"
}
string(41) "System/User/registration/after fired with"
array(1) {
  ["id"]=>
  int(5)
}
array(3) {
  ["reg_key"]=>
  bool(true)
  ["password"]=>
  string(4) "%s"
  ["id"]=>
  int(5)
}
int(5)
string(12) "mr2@test.com"
string(117) "Register new user without confirmation necessary and with automatic sign-in according to system configuration (false)"
string(42) "System/User/registration/before fired with"
array(1) {
  ["email"]=>
  string(12) "mr3@test.com"
}
string(41) "System/User/registration/after fired with"
array(1) {
  ["id"]=>
  int(6)
}
array(3) {
  ["reg_key"]=>
  bool(true)
  ["password"]=>
  string(4) "%s"
  ["id"]=>
  int(6)
}
int(1)
string(88) "Register new user with system confirmation settings (true) and without automatic sign-in"
string(42) "System/User/registration/before fired with"
array(1) {
  ["email"]=>
  string(12) "mr4@test.com"
}
string(41) "System/User/registration/after fired with"
array(1) {
  ["id"]=>
  int(7)
}
array(3) {
  ["reg_key"]=>
  string(32) "%s"
  ["password"]=>
  string(0) ""
  ["id"]=>
  int(7)
}
int(1)
string(89) "Register new user with system confirmation settings (false) and without automatic sign-in"
string(42) "System/User/registration/before fired with"
array(1) {
  ["email"]=>
  string(12) "mr5@test.com"
}
string(41) "System/User/registration/after fired with"
array(1) {
  ["id"]=>
  int(8)
}
array(3) {
  ["reg_key"]=>
  bool(true)
  ["password"]=>
  string(4) "%s"
  ["id"]=>
  int(8)
}
int(1)
string(60) "Cancel registration in System/User/registration/before event"
string(42) "System/User/registration/before fired with"
array(1) {
  ["email"]=>
  string(12) "mr6@test.com"
}
bool(false)
int(1)
string(59) "Cancel registration in System/User/registration/after event"
string(42) "System/User/registration/before fired with"
array(1) {
  ["email"]=>
  string(12) "mr6@test.com"
}
string(41) "System/User/registration/after fired with"
array(1) {
  ["id"]=>
  int(9)
}
bool(false)
int(1)
string(15) "Incorrect email"
bool(false)
int(1)
string(14) "Existing email"
string(6) "exists"
int(1)
string(24) "Delete unconfirmed users"
string(42) "System/User/registration/before fired with"
array(1) {
  ["email"]=>
  string(12) "mr7@test.com"
}
string(41) "System/User/registration/after fired with"
array(1) {
  ["id"]=>
  int(10)
}
string(12) "mr7@test.com"
string(42) "System/User/registration/before fired with"
array(1) {
  ["email"]=>
  string(12) "mr8@test.com"
}
string(41) "System/User/registration/after fired with"
array(1) {
  ["id"]=>
  int(11)
}
bool(false)
