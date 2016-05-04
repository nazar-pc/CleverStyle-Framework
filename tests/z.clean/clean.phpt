--SKIPIF--
<?php
if (!is_dir("$root/cscms.travis")) {
	die('Installation directory not found');
}
?>
--FILE--
<?php
if (include __DIR__.'/../_clean.php') {
	echo 'Done';
}
?>
--EXPECT--
Done
