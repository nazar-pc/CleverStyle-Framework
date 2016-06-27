--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
define('DIR', make_tmp_dir());
Core::instance();
?>
--EXPECT--
Config file not found, is system installed properly?
How to install CleverStyle Framework: https://github.com/nazar-pc/CleverStyle-Framework/tree/master/docs/installation/Installation.md
