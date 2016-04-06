<?php
/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Uploader;
use
	cs\Config,
	cs\DB,
	cs\Event,
	cs\Storage;
/**
 * Supports next events:
 *  System/upload_files/del_tag
 *  [
 *   'url' => url, //Optional
 *   'tag' => tag  //Optional ("%" symbol may be used at the end of string to delete all files, that starts from specified string)
 *  ]
 */
Event::instance()
	->on(
		'admin/System/components/modules/uninstall/before',
		function ($data) {
			if ($data['name'] != 'Uploader') {
				return;
			}
			$module_data = Config::instance()->module('Uploader');
			$storage     = Storage::instance()->storage($module_data->storage('files'));
			$cdb         = DB::instance()->db($module_data->db('files'));
			unset($module_data);
			if (!$storage || !$cdb) {
				return;
			}
			$files = $cdb->q(
				"SELECT `source`
				FROM `[prefix]uploader_files`"
			);
			while ($f = $cdb->f($files, true)) {
				$storage->unlink($f);
			}
			if ($storage->is_dir('Uploader')) {
				$storage->rmdir('Uploader');
			}
		}
	)
	->on(
		'System/upload_files/del_tag',
		function ($data) {
			if (!isset($data['url']) && !isset($data['tag'])) {
				return;
			}
			$module_data = Config::instance()->module('Uploader');
			$storage     = Storage::instance()->storage($module_data->storage('files'));
			if (isset($data['url']) && mb_strpos($data['url'], $storage->base_url()) !== 0) {
				return;
			}
			$cdb = DB::instance()->db_prime($module_data->db('files'));
			if (isset($data['url']) && !isset($data['tag'])) {
				$cdb->q(
					"DELETE FROM `[prefix]uploader_files_tags`
					WHERE `id` IN(
						SELECT `id`
						FROM `[prefix]uploader_files`
						WHERE `url` = '%s'
					)",
					$data['url']
				);
			} elseif (isset($data['url'], $data['tag'])) {
				$cdb->q(
					"DELETE FROM `[prefix]uploader_files_tags`
					WHERE
						`id`	IN(
							SELECT `id`
							FROM `[prefix]uploader_files`
							WHERE `url` = '%s'
						) AND
						`tag`	= '%s'",
					$data['url'],
					$data['tag']
				);
			} else {
				$cdb->q(
					"DELETE FROM `[prefix]uploader_files_tags`
					WHERE `tag` LIKE '%s'",
					$data['tag']
				);
			}
		}
	);
