--TEST--
Building distributive of system core
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
$version	= json_decode(file_get_contents(__DIR__.'/../../components/modules/System/meta.json'), true)['version'];
unlink(__DIR__."/../../CleverStyle_CMS_$version.phar.php");
?>
