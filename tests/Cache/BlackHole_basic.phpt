--TEST--
Basic features using BlackHole cache engine
--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
Core::instance_stub(
	[
		'cache_engine' => 'BlackHole'
	]
);
$Cache = Cache::instance();
if (!$Cache->cache_state()) {
	die('Cache state check failed');
}
$value = uniqid('cache', true);
if (!$Cache->set('test', $value)) {
	die('::set() failed');
}
if ($Cache->test !== false) {
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
	die('::cache_state() method does not work');
}
if ($Cache->test !== false) {
	die('Value still exists');
}
if ($Cache->get('xuz', function () {return 5;}) !== 5) {
	die('Callback is not called for disabled cache');
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
