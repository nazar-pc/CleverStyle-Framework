--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Key = Key::instance();
$db  = DB::instance()->db_prime(0);

$key_1 = $Key->generate(0);
var_dump('Key generated with DB index', $key_1);
$key_2 = $Key->generate($db);
var_dump('Key generated with DB object', $key_2);
var_dump('Are keys equal?', $key_1 !== $key_2);

$data = ['some data', 10, true];
var_dump('Put data with first key and DB index');
var_dump($Key->add(0, $key_1, $data) === $key_1);

var_dump('Put data with second key and DB object');
var_dump($Key->add($db, $key_2, $data) === $key_2);

var_dump('Get data with first key and DB index', $Key->get(0, $key_1, true) === $data);
var_dump('Get data with first key and DB index (repeat)', $Key->get(0, $key_1));

var_dump('Get data with second key and DB object', $Key->get($db, $key_2, true) === $data);
var_dump('Get data with second key and DB object (repeat)', $Key->get($db, $key_2));

$key_3 = $Key->add(0, false, $data);
var_dump('Automatically generated key', $key_3);
var_dump('Get key without data', $Key->get(0, $key_3));

$key_4 = $Key->add(0, false);
var_dump('Automatically generate key without data', $key_4);
var_dump('Delete key without getting data with DB index');
var_dump($Key->del(0, $key_4), $Key->get(0, $key_4));

$key_5 = $Key->add(0, false);
var_dump('Automatically generate key without data', $key_5);
var_dump('Delete key without getting data with DB object');
var_dump($Key->del($db, $key_5), $Key->get(0, $key_5));

var_dump('Wrong key for creation');
var_dump($Key->add(0, ''), $Key->add(0, true), $Key->add(0, null), $Key->add(0, 'bla bla bla'));

var_dump('Wrong key for getting');
var_dump($Key->get(0, ''), $Key->get(0, true), $Key->get(0, null), $Key->get(0, 'bla bla bla'));

var_dump('Wrong key for deletion');
var_dump($Key->del(0, ''), $Key->del(0, true), $Key->del(0, null), $Key->del(0, 'bla bla bla'));

Config::instance_stub(
	[
		'core' => [
			'inserts_limit' => 1,
			'key_expire'    => 4
		]
	]
);
function time ($time = 0) {
	static $stored_time;
	if (!isset($stored_time)) {
		$stored_time = \time();
	}
	if ($time) {
		$stored_time = $time;
	}
	return $stored_time;
}
var_dump('Expiration test');
$time  = \time();
time($time);
$key_6 = $Key->add(0, false, null, $time + 2);
$key_7 = $Key->add(0, false, null);
$key_8 = $Key->add(0, false, null);
time($time + 3);
var_dump($Key->get(0, $key_6), $Key->get(0, $key_7));
time($time + 5);
var_dump($Key->get(0, $key_8));
?>
--EXPECTF--
string(27) "Key generated with DB index"
string(56) "%s"
string(28) "Key generated with DB object"
string(56) "%s"
string(15) "Are keys equal?"
bool(true)
string(36) "Put data with first key and DB index"
bool(true)
string(38) "Put data with second key and DB object"
bool(true)
string(36) "Get data with first key and DB index"
bool(true)
string(45) "Get data with first key and DB index (repeat)"
bool(false)
string(38) "Get data with second key and DB object"
bool(true)
string(47) "Get data with second key and DB object (repeat)"
bool(false)
string(27) "Automatically generated key"
string(56) "%s"
string(20) "Get key without data"
bool(true)
string(39) "Automatically generate key without data"
string(56) "%s"
string(45) "Delete key without getting data with DB index"
bool(true)
bool(false)
string(39) "Automatically generate key without data"
string(56) "%s"
string(46) "Delete key without getting data with DB object"
bool(true)
bool(false)
string(22) "Wrong key for creation"
bool(false)
bool(false)
bool(false)
bool(false)
string(21) "Wrong key for getting"
bool(false)
bool(false)
bool(false)
bool(false)
string(22) "Wrong key for deletion"
bool(false)
bool(false)
bool(false)
bool(false)
string(15) "Expiration test"
bool(false)
bool(true)
bool(false)
