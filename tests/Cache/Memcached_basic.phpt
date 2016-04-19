--TEST--
Basic features using Memcached cache engine
--SKIPIF--
<?php
if (!class_exists('Memcached')) {
	exit('skip Memcached extension is not installed');
}
?>
--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
Core::instance_stub(
	[
		'cache_engine'   => 'Memcached',
		'memcached_host' => '127.0.0.1',
		'11211'          => '11211'
	]
);
require __DIR__.'/_test.php';
?>
--EXPECT_EXTERNAL--
_test.expect
