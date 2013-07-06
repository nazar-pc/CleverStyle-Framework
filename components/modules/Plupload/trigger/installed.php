<?php
/**
 * @package		Plupload
 * @category	modules
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU GPL v2, see license.txt
 */
namespace cs\modules\Plupload;
global $Core;
$Core->register_trigger(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		if ($data['name'] != 'Plupload') {
			return;
		}
		global $Config, $Storage, $db;
		$module_data	= $Config->module('Plupload');
		$storage		= $Storage->{$module_data->storage('files')};
		$cdb			= $db->{$module_data->db('files')};
		unset($module_data);
		if (!$storage || !$cdb) {
			return;
		}
		$files			= $cdb->q(
			"SELECT `source`
			FROM `[prefix]plupload_files`"
		);
		while ($f = $cdb->fs($files)) {
			$storage->unlink($f);
		}
		$storage->rmdir('Plupload');
		clean_pcache();
	}
);
if (!function_exists(__NAMESPACE__.'\\clean_pcache')) {
	function clean_pcache () {
		if (file_exists(PCACHE.'/module.Plupload.js')) {
			unlink(PCACHE.'/module.Plupload.js');
		}
	}
}