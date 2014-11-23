--TEST--
Building distributive of system core without removal
--ARGS--
-M core
--FILE--
<?php
include __DIR__.'/../../build.php';
?>
--EXPECTF--
Done! CleverStyle CMS %s+build-%d
