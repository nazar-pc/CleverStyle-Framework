--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Event = Event::instance();
$User  = User::instance();

var_dump('Registration confirmation');
$result = $User->registration('mrcc1@test.com', true, false);
var_dump($result, $User->get('password_hash', $result['id']));
var_dump($User->registration_confirmation($result['reg_key']));
var_dump($User->get('id', $result['id']), $User->get('password_hash', $result['id']));

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
var_dump($User->registration_confirmation($result['reg_key']), $User->get('id', $result['id']), $User->get('password_hash', $result['id']));

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
var_dump($User->get('id', $result['id']), $User->get('password_hash', $result['id']));

var_dump('Generate password during confirmation');
$result = $User->registration('mrcc4@test.com', true, false);
var_dump($result, $User->get('password_hash', $result['id']));
var_dump($User->registration_confirmation($result['reg_key']));
var_dump($User->get('id', $result['id']), $User->get('password_hash', $result['id']));

?>
--EXPECTF--
string(25) "Registration confirmation"
array(3) {
  ["reg_key"]=>
  string(32) "%s"
  ["password"]=>
  string(0) ""
  ["id"]=>
  int(11)
}
string(0) ""
array(3) {
  ["id"]=>
  int(11)
  ["email"]=>
  string(14) "mrcc1@test.com"
  ["password"]=>
  string(4) "%s"
}
int(11)
string(60) "%s"
string(22) "Wrong confirmation key"
bool(false)
string(73) "Cancel registration in System/User/registration/confirmation/before event"
array(3) {
  ["reg_key"]=>
  string(32) "%s"
  ["password"]=>
  string(0) ""
  ["id"]=>
  int(12)
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
  int(13)
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
  int(14)
}
string(0) ""
array(3) {
  ["id"]=>
  int(14)
  ["email"]=>
  string(14) "mrcc4@test.com"
  ["password"]=>
  string(4) "%s"
}
int(14)
string(60) "%s"
