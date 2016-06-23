--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
Core::instance_stub(['cache_engine' => 'FileSystem']);
define('CACHE', make_tmp_dir());
require __DIR__.'/_test.php';
?>
--EXPECT--
<?php
require __DIR__.'/_test.expect';
?>
--CLEAN--
<?php
include __DIR__.'/../../bootstrap.php';
exec('rm -r '.CACHE.'/*');
