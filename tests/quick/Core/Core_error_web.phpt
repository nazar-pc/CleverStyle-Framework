--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
define('DIR', make_tmp_dir());
function php_sapi_name () {
	return 'apache2handler';
}

Core::instance();
?>
--EXPECT--
<!doctype html>
<p>Config file not found, is system installed properly?</p>
<a href="https://github.com/nazar-pc/CleverStyle-Framework/tree/master/docs/installation/Installation.md">How to install CleverStyle Framework</a>
