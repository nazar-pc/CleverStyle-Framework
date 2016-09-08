--SKIPIF--
<?php
if (getenv('SKIP_SLOW_TESTS')) {
	exit('skip slow test');
}
if (getenv('DB') != 'MySQLi') {
	exit('skip only running for database MySQLi engine');
}
?>
--INI--
phar.readonly = Off
--ARGS--
-M module -m System
--FILE--
<?php
include __DIR__.'/../../code_coverage.php';
include __DIR__.'/../../../build.php';
?>
--EXPECTF--
Can't build module, System module is a part of core, it is not necessary to build it as separate module
