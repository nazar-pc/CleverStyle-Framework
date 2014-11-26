--TEST--
Clean installed files
--SKIPIF--
<?php
if (!is_dir("$root/cscms.travis")) {
	die('Installation directory not found');
}
?>
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
/**
 * @var DB\_Abstract $cdb
 */
$cdb	= DB::instance();
foreach ($cdb->tables() as $table) {
	if (!$cdb->q("DROP TABLE `$table`")) {
		echo "Dropping DB table `$table` failed\n";
	}
}
$root	= __DIR__.'/../..';
if (!exec("rm -r $root/cscms.travis")) {
	echo "Done";
}
?>
--EXPECT--
Done
