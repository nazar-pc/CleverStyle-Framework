<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Config,
	cs\Core,
	cs\DB;

trait components_manipulation {
	/**
	 * Generic extraction of files from phar distributive for CleverStyle CMS (system and components)
	 *
	 * @param string      $target_directory
	 * @param string      $source_phar             Will be removed after extraction
	 * @param null|string $fs_location_directory   Defaults to `$target_directory`
	 * @param null|string $meta_location_directory Defaults to `$target_directory`
	 *
	 * @return bool
	 */
	static protected function update_extract ($target_directory, $source_phar, $fs_location_directory = null, $meta_location_directory = null) {
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
		$tmp_dir = "phar://$source_phar";
		$fs      = file_get_json("$tmp_dir/fs.json");
		$extract = array_product(
			array_map(
				function ($index, $file) use ($tmp_dir, $target_directory) {
					if (
						!file_exists(dirname("$target_directory/$file")) &&
						!mkdir(dirname("$target_directory/$file"), 0770, true)
					) {
						return 0;
					}
					return (int)copy("$tmp_dir/fs/$index", "$target_directory/$file");
				},
				$fs,
				array_keys($fs)
			)
		);
		unlink($source_phar);
		unset($tmp_dir);
		if (!$extract) {
			return false;
		}
		unset($extract);
		$fs = array_keys($fs);
		/**
		 * Removing of old unnecessary files and directories
		 */
		foreach (
			array_diff(
				file_get_json("$fs_location_directory/fs.json"),
				$fs
			) as $file
		) {
			$file = "$target_directory/$file";
			if (file_exists($file) && is_writable($file)) {
				unlink($file);
				// Recursively remove all empty parent directories
				while (!get_files_list($file = dirname($file))) {
					rmdir($file);
				}
			}
		}
		unset($file, $dir);
		file_put_json("$fs_location_directory/fs.json", $fs);
		/**
		 * Removing backups after successful update
		 */
		unlink("$fs_location_directory/fs_backup.json");
		unlink("$meta_location_directory/meta_backup.json");
		return true;
	}
	/**
	 * Generic update for CleverStyle CMS (system and components), runs PHP scripts and does DB migrations after extracting of new distributive
	 *
	 * @param string     $target_directory
	 * @param string     $old_version
	 * @param array|null $db_array `$module_data['db']` if module or system
	 */
	static protected function update_php_sql ($target_directory, $old_version, $db_array = null) {
		$Core   = Core::instance();
		$Config = Config::instance();
		$db     = DB::instance();
		foreach (file_get_json("$target_directory/versions.json") as $version) {
			if (version_compare($old_version, $version, '<')) {
				/**
				 * PHP update script
				 */
				_include("$target_directory/meta/update/$version.php", true, false);
				/**
				 * Database update
				 */
				if ($db_array && file_exists("$target_directory/meta/db.json")) {
					$db_json = file_get_json("$target_directory/meta/db.json");
					time_limit_pause();
					foreach ($db_json as $database) {
						if ($db_array[$database] == 0) {
							$db_type = $Core->db_type;
						} else {
							$db_type = $Config->db[$db_array[$database]]['type'];
						}
						$sql_file = "$target_directory/meta/update_db/$database/$version/$db_type.sql";
						if (isset($db_array[$database]) && file_exists($sql_file)) {
							$db->{$db_array[$database]}()->q(
								explode(';', file_get_contents($sql_file))
							);
						}
					}
					unset($db_json, $database, $db_type, $sql_file);
					time_limit_pause(false);
				}
			}
		}
	}
	/**
	 * @param string $target_directory
	 *
	 * @return bool
	 */
	static protected function recursive_directory_removal ($target_directory) {
		$ok = true;
		get_files_list(
			$target_directory,
			false,
			'fd',
			true,
			true,
			false,
			false,
			true,
			function ($item) use (&$ok) {
				if (is_writable($item)) {
					is_dir($item) ? rmdir($item) : unlink($item);
				} else {
					$ok = false;
				}
			}
		);
		if ($ok) {
			rmdir($target_directory);
		}
		return $ok;
	}
}
