--FILE--
<?php
namespace cs\modules\System\api\Controller {
	function get_timezones_list () {
		return ['UTC (+00:00)' => 'UTC'];
	}
}
namespace cs {
	include __DIR__.'/../../../../bootstrap.php';
	var_dump('Timezones');
	do_api_request(
		'get',
		'api/System/timezones'
	);
}
?>
--EXPECT--
string(9) "Timezones"
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(22) "{"UTC (+00:00)":"UTC"}"
