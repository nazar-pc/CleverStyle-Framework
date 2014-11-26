--TEST--
Building distributive of system core with built-in module, plugin and theme
--INI--
phar.readonly	= Off
--ARGS--
-M core -m Blogs -p TinyMCE -t DarkEnergy
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
