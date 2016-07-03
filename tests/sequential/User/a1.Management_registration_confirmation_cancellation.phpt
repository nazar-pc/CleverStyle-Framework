--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Event = Event::instance();
$User  = User::instance();

var_dump('Registration confirmation');
$result = $User->registration('mrcc1@test.com', true, false);
var_dump('Find unconfirmed user');
var_dump($User->search_users('mrcc1@test.com'));
var_dump($result, $User->get('password_hash', $result['id']));
var_dump($User->registration_confirmation($result['reg_key']));
var_dump((int)$User->get('id', $result['id']) ?: false, $User->get('password_hash', $result['id']));
var_dump('Find confirmed user');
var_dump($User->search_users('mrcc1@test.com'));

var_dump('Wrong confirmation key');
var_dump($User->registration_confirmation('abc'));

var_dump('Cancel registration in System/User/registration/confirmation/before event');
$Event->once(
	'System/User/registration/confirmation/before',
	function () {
		return false;
	}
);
$result = $User->registration('mrcc2@test.com', true, false);
var_dump($result, $User->get('password_hash', $result['id']));
var_dump($User->registration_confirmation($result['reg_key']));
var_dump((int)$User->get('id', $result['id']) ?: false, $User->get('password_hash', $result['id']));

var_dump('Cancel registration in System/User/registration/confirmation/after event');
$Event->once(
	'System/User/registration/confirmation/after',
	function () {
		return false;
	}
);
$result = $User->registration('mrcc3@test.com', true, false);
var_dump($result, $User->get('password_hash', $result['id']));
var_dump($User->registration_confirmation($result['reg_key']));
var_dump((int)$User->get('id', $result['id']) ?: false, $User->get('password_hash', $result['id']));

var_dump('Generate password during confirmation');
$result = $User->registration('mrcc4@test.com', true, false);
var_dump($result, $User->get('password_hash', $result['id']));
var_dump($User->registration_confirmation($result['reg_key']));
var_dump((int)$User->get('id', $result['id']) ?: false, $User->get('password_hash', $result['id']));

var_dump('Confirm with non-existing key');
var_dump($User->registration_confirmation(md5(random_bytes(1000))));

var_dump('Delete non-existing users');
$Event->on(
	'System/User/del/before',
	function ($data) {
		var_dump('System/User/del/before with', $data);
	}
);
$User->del_user(0);
$User->del_user(PHP_INT_MAX);

var_dump('Try to delete guest');
$User->del_user(User::GUEST_ID);

var_dump('Try to delete root administrator');
$User->del_user(User::ROOT_ID);
?>
--EXPECTF--
string(25) "Registration confirmation"
string(21) "Find unconfirmed user"
bool(false)
array(3) {
  ["reg_key"]=>
  string(32) "%s"
  ["password"]=>
  string(0) ""
  ["id"]=>
  int(12)
}
string(0) ""
array(3) {
  ["id"]=>
  int(12)
  ["email"]=>
  string(14) "mrcc1@test.com"
  ["password"]=>
  string(4) "%s"
}
int(12)
string(60) "%s"
string(19) "Find confirmed user"
array(1) {
  [0]=>
  int(12)
}
string(22) "Wrong confirmation key"
bool(false)
string(73) "Cancel registration in System/User/registration/confirmation/before event"
array(3) {
  ["reg_key"]=>
  string(32) "%s"
  ["password"]=>
  string(0) ""
  ["id"]=>
  int(13)
}
string(0) ""
bool(false)
bool(false)
bool(false)
string(72) "Cancel registration in System/User/registration/confirmation/after event"
array(3) {
  ["reg_key"]=>
  string(32) "%s"
  ["password"]=>
  string(0) ""
  ["id"]=>
  int(14)
}
string(0) ""
bool(false)
bool(false)
bool(false)
string(37) "Generate password during confirmation"
array(3) {
  ["reg_key"]=>
  string(32) "%s"
  ["password"]=>
  string(0) ""
  ["id"]=>
  int(15)
}
string(0) ""
array(3) {
  ["id"]=>
  int(15)
  ["email"]=>
  string(14) "mrcc4@test.com"
  ["password"]=>
  string(4) "%s"
}
int(15)
string(60) "%s"
string(29) "Confirm with non-existing key"
bool(false)
string(25) "Delete non-existing users"
string(19) "Try to delete guest"
string(32) "Try to delete root administrator"
