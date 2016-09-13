--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
$User = User::instance();
/** @noinspection OffsetOperationsInspection */
$user_id    = $User->registration('pp@test.com', false, false)['id'];
$session_id = Session::instance()->add($user_id);
$user_data  = $User->get(['login', 'username', 'language', 'timezone', 'avatar'], $user_id);
$cookie     = ['session' => $session_id];

var_dump('Update user profile (no data)');
do_api_request(
	'patch',
	'api/System/profile'
);

var_dump('Update user profile (empty login)');
do_api_request(
	'patch',
	'api/System/profile',
	['login' => ''] + $user_data
);

var_dump('Update guest profile');
do_api_request(
	'patch',
	'api/System/profile',
	$user_data
);

var_dump('Update user profile (already used login)');
do_api_request(
	'patch',
	'api/System/profile',
	['login' => $User->get('login', User::ROOT_ID)] + $user_data,
	[],
	$cookie
);

var_dump("Update user profile (someone else's email as login)");
do_api_request(
	'patch',
	'api/System/profile',
	['login' => 'email@example.com'] + $user_data,
	[],
	$cookie
);

var_dump('Update user profile (own email as login)');
do_api_request(
	'patch',
	'api/System/profile',
	['login' => $User->get('email', $user_id)] + $user_data,
	[],
	$cookie
);

var_dump('Update user profile (correct login)');
do_api_request(
	'patch',
	'api/System/profile',
	['login' => 'superman'] + $user_data,
	[],
	$cookie
);
var_dump($User->get('login', $user_id));

var_dump('Update user profile (supported language)');
do_api_request(
	'patch',
	'api/System/profile',
	['language' => 'English'] + $user_data,
	[],
	$cookie
);
var_dump($User->get('language', $user_id));

var_dump('Update user profile (unsupported language)');
do_api_request(
	'patch',
	'api/System/profile',
	['language' => 'Klingon'] + $user_data,
	[],
	$cookie
);
var_dump($User->get('language', $user_id));

$User->del_user($user_id);

?>
--EXPECT--
string(29) "Update user profile (no data)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(51) "{"error":400,"error_description":"400 Bad Request"}"
string(33) "Update user profile (empty login)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(51) "{"error":400,"error_description":"400 Bad Request"}"
string(20) "Update guest profile"
int(403)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(49) "{"error":403,"error_description":"403 Forbidden"}"
string(40) "Update user profile (already used login)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(61) "{"error":400,"error_description":"Login is already occupied"}"
string(51) "Update user profile (someone else's email as login)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(61) "{"error":400,"error_description":"Login is already occupied"}"
string(40) "Update user profile (own email as login)"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(4) "null"
string(35) "Update user profile (correct login)"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(4) "null"
string(8) "superman"
string(40) "Update user profile (supported language)"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(4) "null"
string(7) "English"
string(42) "Update user profile (unsupported language)"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(4) "null"
string(0) ""
