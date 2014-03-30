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
namespace	cs\modules\Plupload;
use			cs\Config,
			cs\DB,
			cs\Page,
			cs\Storage,
			cs\Trigger;
Trigger::instance()
	->register(
		'System/Page/pre_display',
		function () {
			$Config	= Config::instance();
			$Page	= Page::instance();
			$Page->js(
				'cs.plupload = '._json_encode([
					'max_file_size'	=> $Config->module('Plupload')->max_file_size
				]).';',
				'code'
			);
		}
	)
	->register(
		'System/Index/mainmenu',
		function ($data) {
			if ($data['path'] == 'Plupload') {
				$data['hide']	= true;
				return false;
			}
			return true;
		}
	)
	->register(
		'System/upload_files/add_tag',
		function ($data) {
			if (!isset($data['url'], $data['tag'])) {
				return false;
			}
			$module_data		= Config::instance()->module('Plupload');
			$storage			= Storage::instance()->{$module_data->storage('files')};
			if (mb_strpos($data['url'], $storage->base_url()) !== 0) {
				return false;
			}
			$cdb				= DB::instance()->{$module_data->db('files')}();
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
	)
	->register(
		'System/upload_files/del_tag',
		function ($data) {
			if (!isset($data['url']) && !isset($data['tag'])) {
				return false;
			}
			$module_data		= Config::instance()->module('Plupload');
			$storage			= Storage::instance()->{$module_data->storage('files')};
			if (isset($data['url']) && mb_strpos($data['url'], $storage->base_url()) !== 0) {
				return false;
			}
			$cdb				= DB::instance()->{$module_data->db('files')}();
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
