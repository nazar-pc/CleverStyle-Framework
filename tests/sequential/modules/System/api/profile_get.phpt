--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
$Config                           = Config::instance();
$Config->core['multilingual']     = 1;
$Config->core['active_languages'] = [
	'English',
	'Ukrainian'
];

var_dump('Get guest profile (en)');
do_api_request(
	'get',
	'api/System/profile'
);

var_dump('Get guest profile (uk)');
do_api_request(
	'get',
	'uk/api/System/profile'
);

var_dump('Root user profile');
do_api_request(
	'get',
	'api/System/profile',
	[],
	[],
	[
		'session' => Session::instance()->add(User::ROOT_ID)
	]
);
?>
--EXPECTF--
string(22) "Get guest profile (en)"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(123) "{"id":1,"login":"guest","username":"Guest","language":"","timezone":"","avatar":"http://cscms.travis/assets/img/guest.svg"}"
string(22) "Get guest profile (uk)"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(128) "{"id":1,"login":"guest","username":"Гість","language":"","timezone":"","avatar":"http://cscms.travis/assets/img/guest.svg"}"
string(17) "Root user profile"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(78) "{"id":2,"login":"admin","username":"","language":"","timezone":"","avatar":""}"
