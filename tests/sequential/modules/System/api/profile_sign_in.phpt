--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
$Config = Config::instance();
$User   = User::instance();
/** @noinspection OffsetOperationsInspection */
$user_id = $User->registration('cp@test.com', false, false)['id'];
$User->set_password('1111', $user_id);
$session_id = Session::instance()->add($user_id);
$cookie     = ['session' => $session_id];
$data       = [
	'login'    => $User->get('login_hash', $user_id),
	'password' => hash('sha512', hash('sha512', 1111).Core::instance()->public_key)
];

var_dump('Sign in (no data)');
do_api_request(
	'sign_in',
	'api/System/profile'
);

var_dump('Sign in (already signed in)');
do_api_request(
	'sign_in',
	'api/System/profile',
	$data,
	[],
	$cookie
);

var_dump('Sign in (site is open)');
do_api_request(
	'sign_in',
	'api/System/profile',
	$data
);

var_dump('Sign in (site is closed)');
$Config->core['site_mode'] = 0;
do_api_request(
	'sign_in',
	'api/System/profile',
	$data
);
$Config->core['site_mode'] = 1;

var_dump('Sign in (user is not activated)');
$User->set('status', User::STATUS_NOT_ACTIVATED, $user_id);
do_api_request(
	'sign_in',
	'api/System/profile',
	$data
);

var_dump('Sign in (user is not active)');
$User->set('status', User::STATUS_INACTIVE, $user_id);
do_api_request(
	'sign_in',
	'api/System/profile',
	$data
);
$User->set('status', User::STATUS_ACTIVE, $user_id);

var_dump('Sign in (wrong password)');
$Config->core['sign_in_attempts_block_count'] = 3;
$Config->core['sign_in_attempts_block_time']  = 1000;
do_api_request(
	'sign_in',
	'api/System/profile',
	['password' => 'foo'] + $data
);
do_api_request(
	'sign_in',
	'api/System/profile',
	['password' => 'foo'] + $data
);
do_api_request(
	'sign_in',
	'api/System/profile',
	['password' => 'foo'] + $data
);

var_dump('Sign in (already blocked)');
do_api_request(
	'sign_in',
	'api/System/profile',
	$data
);

$User->del_user($user_id);
?>
--EXPECTF--
string(17) "Sign in (no data)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(51) "{"error":400,"error_description":"400 Bad Request"}"
string(27) "Sign in (already signed in)"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(4) "null"
string(22) "Sign in (site is open)"
int(200)
array(2) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(118) "session=%s; path=/; expires=%s, %d-%s-%d %d:%d:%d GMT; domain=cscms.travis; HttpOnly"
  }
}
string(4) "null"
string(24) "Sign in (site is closed)"
int(200)
array(2) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(118) "session=%s; path=/; expires=%s, %d-%s-%d %d:%d:%d GMT; domain=cscms.travis; HttpOnly"
  }
}
string(4) "null"
string(31) "Sign in (user is not activated)"
int(403)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(85) "{"error":403,"error_description":"Your account is not active or awaiting activation"}"
string(28) "Sign in (user is not active)"
int(403)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(66) "{"error":403,"error_description":"Your account has been disabled"}"
string(24) "Sign in (wrong password)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(126) "{"error":400,"error_description":"Authentication error: sign in information is not correct. Check it, please, and try again."}"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(151) "{"error":400,"error_description":"Authentication error: sign in information is not correct. Check it, please, and try again. Sign in attempts left: 1"}"
int(403)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(86) "{"error":403,"error_description":"Sign in attempts are over, try again in %d minutes"}"
string(25) "Sign in (already blocked)"
int(403)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(86) "{"error":403,"error_description":"Sign in attempts are over, try again in %d minutes"}"
