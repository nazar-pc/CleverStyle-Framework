--TEST--
Debug mode check using FileSystem cache engine
--FILE--
<?php
namespace cs\custom;
use
	cs\Cache,
	cs\Singleton;
include __DIR__.'/../custom_loader.php';
define('DEBUG', true);
class Core {
	use	Singleton;
	function construct () {
		$this->cache_engine	= 'FileSystem';
		$this->cache_size	= 1;
	}
}
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
