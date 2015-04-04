--TEST--
DB get instance for write
--FILE--
<?php
namespace cs;
include __DIR__.'/_bootstrap.php';

var_dump(DB::instance()->db_prime(0));
?>
--EXPECT--
string(34) "Fake engine called with arguments:"
array(6) {
  [0]=>
  string(8) "database"
  [1]=>
  string(4) "user"
  [2]=>
  string(4) "db 0"
  [3]=>
  string(9) "localhost"
  [4]=>
  string(4) "utf8"
  [5]=>
  string(10) "__prefix__"
}
string(19) "Connection: succeed"
object(cs\DB\Fake)#8 (9) {
  ["connected":protected]=>
  bool(true)
  ["db_type":protected]=>
  bool(false)
  ["database":protected]=>
  NULL
  ["prefix":protected]=>
  NULL
  ["time":protected]=>
  NULL
  ["query":protected]=>
  array(2) {
    ["time"]=>
    string(0) ""
    ["text"]=>
    string(0) ""
  }
  ["queries":protected]=>
  array(3) {
    ["num"]=>
    string(0) ""
    ["time"]=>
    array(0) {
    }
    ["text"]=>
    array(0) {
    }
  }
  ["connecting_time":protected]=>
  NULL
  ["async":protected]=>
  bool(false)
}
