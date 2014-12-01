--TEST--
Debug mode check using FileSystem cache engine
--FILE--
<?php
namespace cs;
define('DEBUG', true);
include __DIR__.'/../custom_loader.php';
Core::instance_stub([
	'cache_engine'	=> 'FileSystem',
	'cache_size'	=> 1
]);
$Cache	= Cache::instance();
if ($Cache->cache_state()) {
	die('::cache_state() failed');
}
if (!$Cache->set('test', 5)) {
	die('::set() failed');
}
if ($Cache->test !== false) {
	die('Value still exists');
}
echo 'Done';
?>
--EXPECT--
Done
--CLEAN--
<?php
include __DIR__.'/../custom_loader.php';
exec('rm -r '.CACHE.'/*');
?>
