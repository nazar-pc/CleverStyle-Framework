--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
var_dump('Configuration');
do_api_request(
	'configuration',
	'api/System/profile'
);
?>
--EXPECTF--
string(13) "Configuration"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(195) "{"public_key":"%s","password_min_length":4,"password_min_strength":3}"
