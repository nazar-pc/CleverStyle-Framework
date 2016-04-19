--TEST--
Basic features using APCu cache engine
--SKIPIF--
<?php
if (!function_exists('apc_fetch')) {
	exit('skip APC extension is not installed');
}
?>
--INI--
apc.enable_cli	= 1
--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
Core::instance_stub([
	'cache_engine'	=> 'APC'
]);
$Cache	= Cache::instance();
if (!$Cache->cache_state()) {
	die('Cache state check failed');
}
$value	= uniqid('cache', true);
if (!$Cache->set('test', $value)) {
	die('::set() failed');
}
if ($Cache->test !== $value) {
	die('::get() failed');
}
if (!$Cache->del('test')) {
	die('::del() failed');
}
if ($Cache->get('test') !== false) {
	die('Value still exists');
}
if (!$Cache->set('test', 5)) {
	die('::set() failed (2)');
}
$Cache->disable();
if ($Cache->cache_state() !== false) {
	die('::disable() method does not work');
}
if ($Cache->test !== false) {
	die('Value still exists');
}
?>
Done
--EXPECT--
Done
--CLEAN--
<?php
include __DIR__.'/../bootstrap.php';
exec('rm -r '.CACHE.'/*');
?>
