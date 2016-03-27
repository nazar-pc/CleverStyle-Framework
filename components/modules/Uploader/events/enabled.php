<?php
/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Supports next events:
 *  System/upload_files/add_tag
 *  [
 *   'url' => url, //Required
 *   'tag' => tag  //Required
 *  ]
 */
namespace cs\modules\Uploader;
use
	cs\Config,
	cs\DB,
	cs\Event,
	cs\Page,
	cs\Storage;
Event::instance()
	->on(
		'System/Page/render/before',
		function () {
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
				[
					"SELECT `id`
					FROM `[prefix]uploader_files`
					WHERE `url` = '%s'
					LIMIT 1",
					$data['url']
				]
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
	);
