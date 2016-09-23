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
include __DIR__.'/../../unit.php';
Core::instance_stub(['cache_driver' => 'APCu']);
require __DIR__.'/_test.php';
?>
--EXPECT--
<?php
require __DIR__.'/_test.expect';
