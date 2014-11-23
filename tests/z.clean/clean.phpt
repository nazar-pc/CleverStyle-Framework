--TEST--
Clean installed files
--SKIPIF--
<?php
if (!is_dir("$root/cscms.travis")) {
	die('Installation directory not found');
}
?>
--FILE--
<?php
$root	= __DIR__.'/../..';
if (!exec("rm -r $root/cscms.travis")) {
	echo "Done";
}
?>
--EXPECT--
Done
