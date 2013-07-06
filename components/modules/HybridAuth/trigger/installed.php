<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\HybridAuth;
global $Core;
$Core->register_trigger(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		if ($data['name'] != 'HybridAuth') {
			return;
		}
		clean_pcache();
	}
);
if (!function_exists(__NAMESPACE__.'\\clean_pcache')) {
	function clean_pcache () {
		if (file_exists(PCACHE.'/module.HybridAuth.js')) {
			unlink(PCACHE.'/module.HybridAuth.js');
		}
		if (file_exists(PCACHE.'/module.HybridAuth.css')) {
			unlink(PCACHE.'/module.HybridAuth.css');
		}
	}
}