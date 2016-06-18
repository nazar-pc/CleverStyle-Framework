--SKIPIF--
<?php
if (!is_dir(__DIR__."/../../cscms.travis")) {
	die('skip installation directory not found');
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
