<?php
/**
 * @package		Plupload
 * @category	modules
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU GPL v2, see license.txt
 */
namespace	cs\modules\Plupload;
use
	cs\Config,
	cs\DB,
	cs\Event,
	cs\Storage;
Event::instance()->on(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		if ($data['name'] != 'Plupload') {
			return;
		}
		$module_data	= Config::instance()->module('Plupload');
		$storage		= Storage::instance()->{$module_data->storage('files')};
		$cdb			= DB::instance()->{$module_data->db('files')};
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
		if ($storage->is_dir('Plupload')) {
			$storage->rmdir('Plupload');
		}
	}
);
