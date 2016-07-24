--FILE--
<?php
namespace cs;
include __DIR__.'/_bootstrap.php';

var_dump(DB::instance()->db_prime(0));
?>
--EXPECTF--
string(34) "Fake engine called with arguments:"
array(5) {
  [0]=>
  string(8) "database"
  [1]=>
  string(4) "user"
  [2]=>
  string(4) "db 0"
  [3]=>
  string(9) "localhost"
  [4]=>
  string(10) "__prefix__"
}
string(19) "Connection: succeed"
object(cs\DB\Fake)#%d (%d) {
  ["connected":protected]=>
  bool(true)
  ["db_type":protected]=>
  string(0) ""
  ["database":protected]=>
  NULL
  ["prefix":protected]=>
  NULL
  ["time":protected]=>
  NULL
  ["query":protected]=>
  array(2) {
    ["time"]=>
    int(0)
    ["text"]=>
    string(0) ""
  }
  ["queries":protected]=>
  array(3) {
    ["num"]=>
    int(0)
    ["time"]=>
    array(0) {
    }
    ["text"]=>
    array(0) {
    }
  }
  ["connecting_time":protected]=>
  NULL
  ["in_transaction":protected]=>
  bool(false)
}
