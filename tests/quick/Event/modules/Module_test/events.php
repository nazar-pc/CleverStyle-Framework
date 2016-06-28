<?php
if (!function_exists('module_xyz_test')) {
	function module_xyz_test () {
		var_dump('xyz handler of '.__DIR__);
	}
}
cs\Event::instance()->on('xyz', 'module_xyz_test');
