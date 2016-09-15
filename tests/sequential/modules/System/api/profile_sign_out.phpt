--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
$User = User::instance();
/** @noinspection OffsetOperationsInspection */
$user_id = $User->registration('so@test.com', false, false)['id'];

var_dump('Sign out guest');
do_api_request(
	'sign_out',
	'api/System/profile'
);

var_dump('Sign out');
do_api_request(
	'sign_out',
	'api/System/profile',
	[],
	[],
	['session' => Session::instance()->add($user_id)]
);

$User->del_user($user_id);
?>
--EXPECTF--
string(14) "Sign out guest"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(4) "null"
string(8) "Sign out"
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
    string(76) "session=; path=/; expires=Thu, 01-Jan-1970 00:00:00 GMT; domain=cscms.travis"
    [1]=>
    string(88) "sign_out=1; path=/; expires=%s, %d-%s-%d %d:%d:%d GMT; domain=cscms.travis; HttpOnly"
  }
}
string(4) "null"
