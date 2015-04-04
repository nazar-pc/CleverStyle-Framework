--TEST--
DB MySQLi basic test
--FILE--
<?php
namespace cs;
include __DIR__.'/../../custom_loader.php';

$db    = DB::instance()->db_prime(0);
$query = $db->q(
	"SELECT `domain`
    FROM `[prefix]config`"
);
if (!$query) {
	echo "MySQLi::q() failed\n";
}
if ($db->f($query) !== ['domain' => DOMAIN]) {
	echo "MySQLi::f() failed\n";
}
if ($db->n($query) != 1) {
	echo "MySQLi::n() failed\n";
}
if ($db->affected() != 1) {
	echo "MySQLi::affected() failed\n";
}
if (!$db->q(
	[
		"CREATE TABLE `[prefix]test_table`
			(
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`text` TEXT NOT NULL,
				PRIMARY KEY (`id`)
			)
		ENGINE=InnoDB DEFAULT CHARSET=utf8"
	]
)) {
	echo "Test table creation failed\n";
}
if (!$db->q(
	"INSERT INTO `[prefix]test_table`
		(
			`text`
		) VALUES (
			'%s'
		)",
	'sample string'
)) {
	echo "Sample string insertion failed\n";
}
if ($db->id() !== 1) {
	echo "MySQLi::id() failed\n";
}
$query = $db->q(
	"SELECT `domain`
    FROM `[prefix]config`"
);
$db->free($query);
if ($db->f($query)) {
	echo "MySQLi::free() didn't quite worked\n";
}
?>
Done
--EXPECTF--
Warning: mysqli_result::fetch_array(): Couldn't fetch mysqli_result in /%s/cscms.travis/core/engines/DB/MySQLi.php on line %d
Done
