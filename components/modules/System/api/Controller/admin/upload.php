<?php
/**
 * @package    CleverStyle CMS
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
	cs\Page,
	cs\Session;
trait upload {
	/**
	 * @throws ExitException
	 */
	static function admin_upload_post () {
		if (!isset($_FILES['file']) || !$_FILES['file']['tmp_name']) {
			throw new ExitException(400);
		}
		$L    = Language::instance();
		$file = $_FILES['file'];
		switch ($file['error']) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new ExitException($L->file_too_large, 400);
			case UPLOAD_ERR_NO_TMP_DIR:
				throw new ExitException($L->temporary_folder_is_missing, 400);
			case UPLOAD_ERR_CANT_WRITE:
				throw new ExitException($L->cant_write_file_to_disk, 500);
			case UPLOAD_ERR_PARTIAL:
			case UPLOAD_ERR_NO_FILE:
				throw new ExitException(400);
		}
		$target_directory = TEMP.'/System/admin';
		if (!is_dir($target_directory) && !mkdir($target_directory, 0770, true)) {
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
		if (!move_uploaded_file($file['tmp_name'], $tmp_location)) {
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
		Page::instance()->json($meta);
	}
}
