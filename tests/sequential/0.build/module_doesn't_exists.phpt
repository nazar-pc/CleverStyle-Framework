--SKIPIF--
<?php
if (getenv('SKIP_SLOW_TESTS')) {
	exit('skip slow test');
}
if (getenv('DB') != 'MySQLi') {
	exit('skip only running for database MySQLi driver');
}
?>
--INI--
phar.readonly = Off
--ARGS--
-M module -m Non_existing_module
--FILE--
<?php
include __DIR__.'/../../code_coverage.php';
include __DIR__.'/../../../build.php';
?>
--EXPECTF--
Can't build Non_existing_module, meta information (meta.json) not found
