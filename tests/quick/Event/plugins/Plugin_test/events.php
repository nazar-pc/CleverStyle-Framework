<?php
if (!function_exists('plugin_xyz_test')) {
	function plugin_xyz_test () {
		var_dump('xyz handler of '.__DIR__);
	}
}

cs\Event::instance()->on('xyz', 'plugin_xyz_test');
