--TEST--
Basic features using APC cache engine
--INI--
apc.enable_cli=1
--FILE--
<?php
namespace cs\custom;
use
	cs\Cache,
	cs\Singleton;
include __DIR__.'/../custom_loader.php';
define('DEBUG', false);
class Core {
	use	Singleton;
	function construct () {
		$this->cache_engine	= 'APC';
	}
}
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
echo 'Done';
?>
--EXPECT--
Done
--CLEAN--
<?php
include __DIR__.'/../custom_loader.php';
exec('rm -r '.CACHE.'/*');
?>
