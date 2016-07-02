<?php
/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

Event::instance()
	->on(
		'System/Page/render/before',
		function () {
			if (!Config::instance()->module('Uploader')->enabled()) {
				return;
			}
			$Config        = Config::instance();
			$Page          = Page::instance();
			$module_data   = $Config->module('Uploader');
			$max_file_size = (int)$module_data->max_file_size;
			switch (substr(strtolower($module_data->max_file_size), -2)) {
				case 'gb':
					$max_file_size *= 1024;
				case 'mb':
					$max_file_size *= 1024;
				case 'kb':
					$max_file_size *= 1024;
			}
			$Page->config(
				[
					'max_file_size' => $max_file_size
				],
				'cs.uploader'
			);
		}
	)
	->on(
		'System/upload_files/add_tag',
		function ($data) {
			if (!Config::instance()->module('Uploader')->enabled()) {
				return true;
			}
			if (!isset($data['url'], $data['tag'])) {
				return false;
			}
			$module_data = Config::instance()->module('Uploader');
			$storage     = Storage::instance()->storage($module_data->storage('files'));
			if (mb_strpos($data['url'], $storage->base_url()) !== 0) {
				return false;
			}
			$cdb = DB::instance()->db_prime($module_data->db('files'));
			$id  = $cdb->qfs(
				"SELECT `id`
				FROM `[prefix]uploader_files`
				WHERE `url` = '%s'
				LIMIT 1",
				$data['url']
			);
			if (!$id) {
				return false;
			}
			return $cdb->q(
				"INSERT IGNORE INTO `[prefix]uploader_files_tags`
					(`id`, `tag`)
				VALUES
					('%s', '%s')",
				$id,
				$data['tag']
			);
		}
	)
	->on(
		'admin/System/modules/uninstall/before',
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
	)
	->on(
		'admin/System/modules/install/after',
		function ($data) {
			if ($data['name'] == 'Uploader') {
				$Config                                        = Config::instance();
				$Config->module('Uploader')->max_file_size     = '5mb';
				$Config->module('Uploader')->confirmation_time = '900';
			}
		}
	);
