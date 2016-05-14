<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System;
use
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Language,
	cs\Page,
	cs\Request;

/**
 * Utility functions, necessary during packages manipulation (installation/uninstallation, enabling/disabling)
 */
class Packages_manipulation {
	/**
	 * @param string $file_name File key in `cs\Request::$files`
	 *
	 * @return false|string Path to file location if succeed or `false` on failure
	 */
	static function move_uploaded_file_to_tmp ($file_name) {
		$file = Request::instance()->files($file_name);
		if (!$file) {
			return false;
		}
		$L    = Language::prefix('system_admin_');
		$Page = Page::instance();
		switch ($file['error']) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$Page->warning($L->file_too_large);
				return false;
			case UPLOAD_ERR_NO_TMP_DIR:
				$Page->warning($L->temporary_folder_is_missing);
				return false;
			case UPLOAD_ERR_CANT_WRITE:
				$Page->warning($L->cant_write_file_to_disk);
				return false;
		}
		if ($file['error'] != UPLOAD_ERR_OK) {
			return false;
		}
		$tmp_name = TEMP.'/'.md5(random_bytes(1000)).'.phar';
		return copy($file['tmp_name'], $tmp_name) ? $tmp_name : false;
	}
	/**
	 * Generic extraction of files from phar distributive for CleverStyle CMS (components installation)
	 *
	 * @param string $target_directory
	 * @param string $source_phar Will be removed after extraction
	 *
	 * @return bool
	 */
	static function install_extract ($target_directory, $source_phar) {
		/** @noinspection MkdirRaceConditionInspection */
		if (!mkdir($target_directory, 0770)) {
			return false;
		}
		$tmp_dir   = "phar://$source_phar";
		$fs        = file_get_json("$tmp_dir/fs.json");
		$extracted = array_filter(
			array_map(
				function ($index, $file) use ($tmp_dir, $target_directory) {
					if (
						!@mkdir(dirname("$target_directory/$file"), 0770, true) &&
						!is_dir(dirname("$target_directory/$file"))
					) {
						return false;
					}
					/**
					 * TODO: copy() + file_exists() is a hack for HHVM, when bug fixed upstream (copying of empty files) this should be simplified
					 */
					copy("$tmp_dir/fs/$index", "$target_directory/$file");
					return file_exists("$target_directory/$file");
				},
				$fs,
				array_keys($fs)
			)
		);
		unlink($source_phar);
		if (count($extracted) === count($fs)) {
			file_put_json("$target_directory/fs.json", array_keys($fs));
			return true;
		}
		return false;
	}
	/**
	 * Generic extraction of files from phar distributive for CleverStyle CMS (system and components update)
	 *
	 * @param string      $target_directory
	 * @param string      $source_phar             Will be removed after extraction
	 * @param null|string $fs_location_directory   Defaults to `$target_directory`
	 * @param null|string $meta_location_directory Defaults to `$target_directory`
	 *
	 * @return bool
	 */
	static function update_extract ($target_directory, $source_phar, $fs_location_directory = null, $meta_location_directory = null) {
		$fs_location_directory   = $fs_location_directory ?: $target_directory;
		$meta_location_directory = $meta_location_directory ?: $target_directory;
		/**
		 * Backup some necessary information about current version
		 */
		copy("$fs_location_directory/fs.json", "$fs_location_directory/fs_backup.json");
		copy("$meta_location_directory/meta.json", "$meta_location_directory/meta_backup.json");
		/**
		 * Extracting new versions of files
		 */
		$tmp_dir   = "phar://$source_phar";
		$fs        = file_get_json("$tmp_dir/fs.json");
		$extracted = array_filter(
			array_map(
				function ($index, $file) use ($tmp_dir, $target_directory) {
					if (
						!@mkdir(dirname("$target_directory/$file"), 0770, true) &&
						!is_dir(dirname("$target_directory/$file"))
					) {
						return false;
					}
					/**
					 * TODO: copy() + file_exists() is a hack for HHVM, when bug fixed upstream (copying of empty files) this should be simplified
					 */
					copy("$tmp_dir/fs/$index", "$target_directory/$file");
					return file_exists("$target_directory/$file");
				},
				$fs,
				array_keys($fs)
			)
		);
		unlink($source_phar);
		unset($tmp_dir);
		if (count($extracted) !== count($fs)) {
			return false;
		}
		unset($extract);
		$fs = array_keys($fs);
		/**
		 * Removing of old unnecessary files and directories
		 */
		foreach (
			array_diff(
				file_get_json("$fs_location_directory/fs_backup.json"),
				$fs
			) as $file
		) {
			$file = "$target_directory/$file";
			if (is_writable($file)) {
				unlink($file);
				// Recursively remove all empty parent directories
				while (!get_files_list($file = dirname($file), false, 'fd')) {
					rmdir($file);
				}
			}
		}
		unset($file, $dir);
		file_put_json("$fs_location_directory/fs.json", $fs);
		clearstatcache(true);
		if (function_exists('opcache_reset')) {
			opcache_reset();
		}
		return true;
	}
	/**
	 * Generic update for CleverStyle CMS (system and components), runs PHP scripts and does DB migrations after extracting of new distributive
	 *
	 * @param string     $target_directory
	 * @param string     $old_version
	 * @param array|null $db_array `$Config->components['modules'][$module]['db']` if module or system
	 *
	 * @throws \cs\ExitException
	 */
	static function update_php_sql ($target_directory, $old_version, $db_array = null) {
		foreach (self::get_update_versions($target_directory) as $version) {
			if (version_compare($old_version, $version, '<')) {
				/**
				 * PHP update script
				 */
				_include_once("$target_directory/meta/update/$version.php", false);
				/**
				 * Database update
				 */
				if ($db_array) {
					self::execute_sql_from_directory("$target_directory/meta/update_db", $db_array, $version);
				}
			}
		}
	}
	/**
	 * @param string $target_directory
	 *
	 * @return string[]
	 */
	protected static function get_update_versions ($target_directory) {
		$update_versions = _mb_substr(get_files_list("$target_directory/meta/update"), 0, -4) ?: [];
		foreach (get_files_list("$target_directory/meta/update_db", false, 'd') ?: [] as $db) {
			/** @noinspection SlowArrayOperationsInLoopInspection */
			$update_versions = array_merge(
				$update_versions,
				get_files_list("$target_directory/meta/update_db/$db", false, 'd') ?: []
			);
		}
		$update_versions = array_unique($update_versions);
		usort($update_versions, 'version_compare');
		return $update_versions;
	}
	/**
	 * @param string $directory        Base path to SQL files
	 * @param array  $db_configuration Array in form [$db_name => $index]
	 * @param string $version          In case when we are working with update script we might have version subdirectory
	 *
	 * @throws \cs\ExitException
	 */
	static function execute_sql_from_directory ($directory, $db_configuration, $version = '') {
		$Config = Config::instance();
		$Core   = Core::instance();
		$db     = DB::instance();
		time_limit_pause();
		foreach ($db_configuration as $db_name => $index) {
			$db_type  = $index == 0 ? $Core->db_type : $Config->db[$index]['type'];
			$sql_file = "$directory/$db_name/$version/$db_type.sql";
			if (file_exists($sql_file)) {
				$db->db_prime($index)->q(
					explode(';', file_get_contents($sql_file))
				);
			}
		}
		time_limit_pause(false);
	}
}
