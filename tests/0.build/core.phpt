--TEST--
Building distributive of system core
--SKIPIF--
<?php
if (getenv('SKIP_SLOW_TESTS')) {
	exit('skip slow test');
}
?>
--INI--
phar.readonly    = Off
--ARGS--
-M core
--FILE--
<?php
include __DIR__.'/../../build.php';
?>
--EXPECTF--
Done! CleverStyle CMS %s+build-%d
--CLEAN--
<?php
$version = json_decode(file_get_contents(__DIR__.'/../../components/modules/System/meta.json'), true)['version'];
unlink(__DIR__."/../../CleverStyle_CMS_$version.phar.php");
?>
