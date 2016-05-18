<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\ExitException,
	cs\Language,
	cs\Request,
	cs\Session;

trait upload {
	/**
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function admin_upload_post () {
		$file = Request::instance()->files('file');
		if (!$file) {
			throw new ExitException(400);
		}
		$L = Language::prefix('system_admin_');
		switch ($file['error']) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new ExitException($L->file_too_large, 400);
			case UPLOAD_ERR_NO_TMP_DIR:
				throw new ExitException($L->temporary_folder_is_missing, 400);
			case UPLOAD_ERR_CANT_WRITE:
				throw new ExitException($L->cant_write_file_to_disk, 500);
		}
		if ($file['error'] != UPLOAD_ERR_OK) {
			throw new ExitException(400);
		}
		$target_directory = TEMP.'/System/admin';
		if (!@mkdir($target_directory, 0770, true) && !is_dir($target_directory)) {
			throw new ExitException(500);
		}
		$tmp_filename = Session::instance()->get_id().'.phar';
		$tmp_location = "$target_directory/$tmp_filename";
		// Cleanup
		get_files_list(
			$target_directory,
			'/.*\.phar$/',
			'f',
			true,
			false,
			false,
			false,
			false,
			function ($file) {
				unlink($file);
			}
		);
		if (!copy($file['tmp_name'], $tmp_location)) {
			throw new ExitException(500);
		}
		$tmp_dir = "phar://$tmp_location";
		if (!file_exists("$tmp_dir/meta.json")) {
			unlink($tmp_location);
			throw new ExitException(400);
		}
		$meta = file_get_json("$tmp_dir/meta.json");
		if (!isset($meta['category'], $meta['package'], $meta['version'])) {
			unlink($tmp_location);
			throw new ExitException(400);
		}
		return $meta;
	}
}
