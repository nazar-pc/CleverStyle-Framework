--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
Config::instance_stub(
	[
		'core' => [
			'cookie_prefix' => 'prefixed_'
		]
	]
);
$Request = Request::instance();
$Request->init_cookie(
	[
		'c1'          => 'V',
		'c2'          => 'V2',
		'prefixed_c2' => 'V2 prefixed',
		'prefixed_c3' => 'V3 prefixed',
		'c3'          => 'V3'
	]
);

var_dump('Basic');
var_dump($Request->cookie('c1'));
var_dump($Request->cookie('prefixed_c2'));

var_dump('Override with prefix');
var_dump($Request->cookie('c2'));
var_dump($Request->cookie('c3'));

var_dump('Non-existing');
var_dump($Request->cookie('c4'));
?>
--EXPECT--
string(5) "Basic"
string(1) "V"
string(11) "V2 prefixed"
string(20) "Override with prefix"
string(11) "V2 prefixed"
string(11) "V3 prefixed"
string(12) "Non-existing"
NULL
