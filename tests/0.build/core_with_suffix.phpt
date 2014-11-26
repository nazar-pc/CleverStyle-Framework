--TEST--
Building distributive of system core with suffix
--INI--
phar.readonly	= Off
--ARGS--
-M core -s Core
--FILE--
<?php
include __DIR__.'/../../build.php';
?>
--EXPECTF--
Done! CleverStyle CMS %s+build-%d
--CLEAN--
<?php
$version	= json_decode(file_get_contents(__DIR__.'/../../components/modules/System/meta.json'), true)['version'];
unlink(__DIR__."/../../CleverStyle_CMS_{$version}_Core.phar.php");
?>
