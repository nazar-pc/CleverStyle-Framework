--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
var_dump('Sign in (site is open)');
do_api_request(
	'sign_in',
	'api/System/profile',
	[
		'login'    => hash('sha224', 'admin'),
		'password' => hash('sha512', hash('sha512', 1111).Core::instance()->public_key)
	]
);

var_dump('Sign in (site is closed)');
Config::instance()->core['site_mode'] = 0;
do_api_request(
	'sign_in',
	'api/System/profile',
	[
		'login'    => hash('sha224', 'admin'),
		'password' => hash('sha512', hash('sha512', 1111).Core::instance()->public_key)
	]
);
?>
--EXPECTF--
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
