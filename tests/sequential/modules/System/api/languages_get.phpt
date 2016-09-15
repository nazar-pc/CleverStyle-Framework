--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
var_dump('Active languages');
Config::instance()->core['active_languages'] = [
	'English',
	'Ukrainian'
];
do_api_request(
	'get',
	'api/System/languages'
);
?>
--EXPECT--
string(16) "Active languages"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(23) "["English","Ukrainian"]"
