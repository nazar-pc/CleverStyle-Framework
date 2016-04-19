--TEST--
Basic features using APCu cache engine
--SKIPIF--
<?php
if (!function_exists('apc_fetch')) {
	exit('skip APC extension is not installed');
}
?>
--INI--
apc.enable_cli = 1
--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
Core::instance_stub(['cache_engine' => 'APC']);
require __DIR__.'/_test.php';
?>
--EXPECT_EXTERNAL--
_test.expect
--CLEAN--
<?php
include __DIR__.'/../bootstrap.php';
exec('rm -r '.CACHE.'/*');
?>
