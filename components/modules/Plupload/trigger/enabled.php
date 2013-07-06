<?php
/**
 * @package		Plupload
 * @category	modules
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU GPL v2, see license.txt
 */
/**
 * Supports next triggers:
 *  System/upload_files/add_tag
 *  [
 *   'url'		=> url	//Required
 *   'tag'		=> tag	//Required
 *  ]
 *
 *  System/upload_files/del_tag
 *  [
 *   'url'		=> url	//Optional
 *   'tag'		=> tag	//Optional ("%" symbol may be used at the end of string to delete all files, that starts from specified string)
 *  ]
 */
namespace cs\modules\Plupload;
global $Core;
$Core->register_trigger(
	'admin/System/components/modules/disable',
	function ($data) {
		if ($data['name'] == 'Plupload') {
			clean_pcache();
		}
	}
);
$Core->register_trigger(
	'admin/System/general/optimization/clean_pcache',
	function () {
		clean_pcache();
	}
);
$Core->register_trigger(
	'System/Page/rebuild_cache',
	function ($data) {
		rebuild_pcache($data);
	}
);
function clean_pcache () {
	if (file_exists(PCACHE.'/module.Plupload.js')) {
		unlink(PCACHE.'/module.Plupload.js');
	}
}
function rebuild_pcache (&$data = null) {
	global $Config;
	if (
		!$Config->core['cache_compress_js_css'] ||
		file_exists(PCACHE.'/module.Plupload.js')
	) {
		return;
	}
	$content	= '';
	array_map(
		function ($language) use (&$content) {
			$content	.= "if (lang == '$language') ".file_get_contents(MODULES."/Plupload/includes/js/i18n/$language.js");
		},
		_mb_substr(get_files_list(MODULES.'/Plupload/includes/js/i18n', false, 'f'), 0, -3)
	);
	file_put_contents(
		PCACHE.'/module.Plupload.js',
		$key	= gzencode(
			file_get_contents(MODULES.'/Plupload/includes/js/plupload.js').
			file_get_contents(MODULES.'/Plupload/includes/js/plupload.html5.js').
			file_get_contents(MODULES.'/Plupload/includes/js/integration.js').
			$content,
			9
		),
		LOCK_EX | FILE_BINARY
	);
	if ($data !== null) {
		$data['key']	.= md5($key);
	}
}
$Core->register_trigger(
	'System/Page/pre_display',
	function () {
		global $Config, $Page;
		if (!$Config->core['cache_compress_js_css']) {
			$Page->js([
				'components/modules/Plupload/includes/js/plupload.js',
				'components/modules/Plupload/includes/js/plupload.html5.js',
				'components/modules/Plupload/includes/js/integration.js'
			]);
		} elseif (!file_exists(PCACHE.'/module.Plupload.js')) {
			rebuild_pcache();
		}
		$Page->js(
			'var	plupload_max_file_size = '._json_encode($Config->module('Plupload')->max_file_size).';',
			'code'
		);
	}
);
$Core->register_trigger(
	'System/Index/mainmenu',
	function ($data) {
		if ($data['path'] == 'Plupload') {
			$data['hide']	= true;
			return false;
		}
		return true;
	}
);
$Core->register_trigger(
	'System/upload_files/add_tag',
	function ($data) {
		if (!isset($data['url'], $data['tag'])) {
			return false;
		}
		global $Config, $db, $Storage;
		$module_data		= $Config->module('Plupload');
		$storage			= $Storage->{$module_data->storage('files')};
		if (mb_strpos($data['url'], $storage->base_url()) !== 0) {
			return false;
		}
		$cdb				= $db->{$module_data->db('files')}();
		$id		= $cdb->qfs([
			"SELECT `id`
			FROM `[prefix]plupload_files`
			WHERE `url` = '%s'
			LIMIT 1",
			$data['url']
		]);
		if (!$id) {
			return false;
		}
		return $cdb->q(
			"INSERT IGNORE INTO `[prefix]plupload_files_tags`
				(`id`, `tag`)
			VALUES
				('%s', '%s')",
			$id,
			$data['tag']
		);
	}
);
$Core->register_trigger(
	'System/upload_files/del_tag',
	function ($data) {
		if (!isset($data['url']) && !isset($data['tag'])) {
			return false;
		}
		global $Config, $db, $Storage;
		$module_data		= $Config->module('Plupload');
		$storage			= $Storage->{$module_data->storage('files')};
		if (isset($data['url']) && mb_strpos($data['url'], $storage->base_url()) !== 0) {
			return false;
		}
		$cdb				= $db->{$module_data->db('files')}();
		if (isset($data['url']) && !isset($data['tag'])) {
			return $cdb->q(
				"DELETE FROM `[prefix]plupload_files_tags`
				WHERE `id` IN(
					SELECT `id`
					FROM `[prefix]plupload_files`
					WHERE `url` = '%s'
					LIMIT 1
				)",
				$data['url']
			);
		} elseif (isset($data['url'], $data['tag'])) {
			return $cdb->q(
				"DELETE FROM `[prefix]plupload_files_tags`
				WHERE
					`id`	IN(
						SELECT `id`
						FROM `[prefix]plupload_files`
						WHERE `url` = '%s'
						LIMIT 1
					) AND
					`tag`	= '%s'",
				$data['url'],
				$data['tag']
			);
		} else {
			return $cdb->q(
				"DELETE FROM `[prefix]plupload_files_tags`
				WHERE `tag` LIKE '%s'",
				$data['tag']
			);
		}
	}
);