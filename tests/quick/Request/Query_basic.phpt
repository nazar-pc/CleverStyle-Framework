--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
$Request = Request::instance();
$Request->init_query(
	[
		'p1' => 'V',
		'p2' => 'V2'
	]
);

var_dump('Single query parameter');
var_dump($Request->query('p1'));

var_dump('Multiple');
var_dump($Request->query('p1', 'p2'));
var_dump($Request->query(['p1', 'p2']));

var_dump('Non-existing');
var_dump($Request->query('p3'));
var_dump($Request->query('p1', 'p2', 'p3'));
var_dump($Request->query(['p1', 'p2', 'p3']));
?>
--EXPECT--
string(22) "Single query parameter"
string(1) "V"
string(8) "Multiple"
array(2) {
  ["p1"]=>
  string(1) "V"
  ["p2"]=>
  string(2) "V2"
}
array(2) {
  ["p1"]=>
  string(1) "V"
  ["p2"]=>
  string(2) "V2"
}
string(12) "Non-existing"
NULL
NULL
NULL
