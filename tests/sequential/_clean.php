<?php
namespace cs;
require_once __DIR__.'/../bootstrap.php';
/**
 * @var DB\_Abstract $cdb
 */
Config::instance_replace(False_class::instance());
$cdb	= DB::instance()->db_prime(0);
foreach ($cdb->tables() as $table) {
	if (!$cdb->q("DROP TABLE `$table`")) {
		echo "Dropping DB table `$table` failed\n";
		return false;
	}
}
$root	= __DIR__.'/../cscms.travis';
// Check for true because in case of success output will be empty
if (exec("rm -r $root")) {
	echo 'Removing files failed';
	return false;
}
return true;
