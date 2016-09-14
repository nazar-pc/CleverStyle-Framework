--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
$User = User::instance();
/** @noinspection OffsetOperationsInspection */
$user_id = $User->registration('cp@test.com', false, false)['id'];
$User->set_password('1111', $user_id);
$session_id = Session::instance()->add($user_id);
$cookie     = ['session' => $session_id];
$public_key = Core::instance()->public_key;

var_dump('Change password (no data)');
do_api_request(
	'change_password',
	'api/System/profile'
);

var_dump('Change password of guest user');
do_api_request(
	'change_password',
	'api/System/profile',
	[
		'current_password' => '',
		'new_password'     => ''
	]
);

var_dump('Change password (no password)');
do_api_request(
	'change_password',
	'api/System/profile',
	[
		'current_password' => '',
		'new_password'     => ''
	],
	[],
	$cookie
);

var_dump('Change password (wrong existing password)');
do_api_request(
	'change_password',
	'api/System/profile',
	[
		'current_password' => '',
		'new_password'     => '1234'
	],
	[],
	$cookie
);

var_dump('Change password (empty password)');
do_api_request(
	'change_password',
	'api/System/profile',
	[
		'current_password' => hash('sha512', hash('sha512', '1111').$public_key),
		'new_password'     => hash('sha512', hash('sha512', '').$public_key)
	],
	[],
	$cookie
);

var_dump('Change password');
do_api_request(
	'change_password',
	'api/System/profile',
	[
		'current_password' => hash('sha512', hash('sha512', '1111').$public_key),
		'new_password'     => hash('sha512', hash('sha512', '1234').$public_key)
	],
	[],
	$cookie
);

$User->del_user($user_id);
?>
--EXPECTF--
string(25) "Change password (no data)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(51) "{"error":400,"error_description":"400 Bad Request"}"
string(29) "Change password of guest user"
int(403)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(49) "{"error":403,"error_description":"403 Forbidden"}"
string(29) "Change password (no password)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(63) "{"error":400,"error_description":"Please enter a new password"}"
string(41) "Change password (wrong existing password)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(69) "{"error":400,"error_description":"The current password is incorrect"}"
string(32) "Change password (empty password)"
int(500)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(130) "{"error":500,"error_description":"Password changing error: fault on the server. Wait for several minutes, please, and try again."}"
string(15) "Change password"
int(200)
array(2) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
  ["set-cookie"]=>
  array(2) {
    [0]=>
    string(76) "session=; path=/; expires=%s, %d-%s-%d %d:%d:%d GMT; domain=cscms.travis"
    [1]=>
    string(118) "session=%s; path=/; expires=%s, %d-%s-%d %d:%d:%d GMT; domain=cscms.travis; HttpOnly"
  }
}
string(4) "null"
