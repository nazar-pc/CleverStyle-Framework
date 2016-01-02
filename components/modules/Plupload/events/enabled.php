<?php
/**
 * @package   Plupload
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   GNU GPL v2, see license.txt
 */
/**
 * Supports next events:
 *  System/upload_files/add_tag
 *  [
 *   'url' => url, //Required
 *   'tag' => tag  //Required
 *  ]
 */
namespace cs\modules\Plupload;
use
	cs\Config,
	cs\DB,
	cs\Event,
	cs\Page,
	cs\Storage;
Event::instance()
	->on(
		'System/Page/display/before',
		function () {
			$Config = Config::instance();
			$Page   = Page::instance();
			$Page->config(
				[
					'max_file_size' => $Config->module('Plupload')->max_file_size
				],
				'cs.plupload'
			);
		}
	)
	->on(
		'System/upload_files/add_tag',
		function ($data) {
			if (!isset($data['url'], $data['tag'])) {
				return false;
			}
			$module_data = Config::instance()->module('Plupload');
			$storage     = Storage::instance()->storage($module_data->storage('files'));
			if (mb_strpos($data['url'], $storage->base_url()) !== 0) {
				return false;
			}
			$cdb = DB::instance()->db_prime($module_data->db('files'));
			$id  = $cdb->qfs(
				[
					"SELECT `id`
				FROM `[prefix]plupload_files`
				WHERE `url` = '%s'
				LIMIT 1",
					$data['url']
				]
			);
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
