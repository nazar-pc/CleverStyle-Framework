--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
Response::instance_stub(
	[
		'code' => 500
	]
);

var_dump('Default ExitException');
$e = new ExitException;
var_dump($e->getMessage(), $e->getCode());

var_dump('ExitException message only');
$e = new ExitException('Some message');
var_dump($e->getMessage(), $e->getCode());

var_dump('ExitException code only (first argument)');
$e = new ExitException(404);
var_dump($e->getMessage(), $e->getCode());

var_dump('ExitException code only (second argument)');
$e = new ExitException('', 404);
var_dump($e->getMessage(), $e->getCode());

var_dump('ExitException code only (second argument)');
$e = new ExitException('', 404);
var_dump($e->getMessage(), $e->getCode());

var_dump('ExitException message and code');
$e = new ExitException('Some message', 404);
var_dump($e->getMessage(), $e->getCode());

var_dump('ExitException JSON');
$e = new ExitException;
var_dump($e->getJson());
$e->setJson();
var_dump($e->getJson());
?>
--EXPECT--
string(21) "Default ExitException"
string(0) ""
int(500)
string(26) "ExitException message only"
string(12) "Some message"
int(500)
string(40) "ExitException code only (first argument)"
string(0) ""
int(404)
string(41) "ExitException code only (second argument)"
string(0) ""
int(404)
string(41) "ExitException code only (second argument)"
string(0) ""
int(404)
string(30) "ExitException message and code"
string(12) "Some message"
int(404)
string(18) "ExitException JSON"
bool(false)
bool(true)
