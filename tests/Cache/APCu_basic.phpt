--TEST--
Basic features using APCu cache engine
--SKIPIF--
<?php
if (!function_exists('apcu_fetch')) {
	exit('skip APCu extension is not installed');
}
?>
--INI--
apc.enable_cli = 1
--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
Core::instance_stub(['cache_engine' => 'APCu']);
require __DIR__.'/_test.php';
?>
--EXPECT_EXTERNAL--
_test.expect
