--SKIPIF--
<?php
if (getenv('SKIP_SLOW_TESTS')) {
	exit('skip slow test');
}
if (getenv('DB') && getenv('DB') != 'MySQLi') {
	exit('skip only running for database MySQLi engine');
}
?>
--INI--
phar.readonly = Off
--ARGS--
-M core -s Core
--FILE--
<?php
include __DIR__.'/../../../build.php';
?>
--EXPECTF--
Done! CleverStyle Framework %s+build-%d
--CLEAN--
<?php
$version = json_decode(file_get_contents(__DIR__.'/../../../components/modules/System/meta.json'), true)['version'];
unlink(__DIR__."/../../../CleverStyle_Framework_{$version}_Core.phar.php");
