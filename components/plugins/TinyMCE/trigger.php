<?php
global $Core;
$clean_pcache = function ($data = null) {
	$plugin = basename(__DIR__);
	if (
		($data['name'] == $plugin || $data === null) &&
		file_exists(PCACHE.'/plugin.'.$plugin.'.js')
	) {
		unlink(PCACHE.'/plugin.'.$plugin.'.js');
	}
};
$Core->register_trigger('admin/System/components/plugins/disable',			$clean_pcache);
$Core->register_trigger('admin/System/general/optimization/clean_pcache',	$clean_pcache);