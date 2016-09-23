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
-M module -m Blogs -s Suffix
--FILE--
<?php
include __DIR__.'/../../code_coverage.php';
include __DIR__.'/../../../build.php';
?>
--EXPECTF--
Done! Module Blogs %s+build-%d
--CLEAN--
<?php
$version = json_decode(file_get_contents(__DIR__.'/../../../modules/Blogs/meta.json'), true)['version'];
unlink(__DIR__."/../../../module_Blogs_{$version}_Suffix.phar.php");
