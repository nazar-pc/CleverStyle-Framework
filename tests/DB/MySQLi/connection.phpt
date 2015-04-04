--TEST--
DB MySQLi connection test
--FILE--
<?php
namespace cs;
include __DIR__.'/../../custom_loader.php';

$db = DB::instance()->db_prime(0);
if (!$db->connected()) {
  echo "Connection failed\n";
}
if ($db->db_type() != 'mysql') {
  echo "Wrong DB type\n";
}
?>
Done
--EXPECT--
Done
