<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * Provides cache functionality based on file system structure.
 */
class FileSystem extends _Abstract {
	/**
	 * Like realpath() but works even if files does not exists
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	protected function get_absolute_path ($path) {
		$path      = CACHE."/$path";
		$path      = str_replace(['/', '\\'], '/', $path);
		$parts     = array_filter(explode('/', $path), 'strlen');
		$absolutes = [];
		foreach ($parts as $part) {
			if ('.' == $part) {
				continue;
			}
			if ('..' == $part) {
				array_pop($absolutes);
			} else {
				$absolutes[] = $part;
			}
		}
		return '/'.implode('/', $absolutes);
	}
	/**
	 * @inheritdoc
	 */
	function get ($item) {
		$path_in_filesystem = $this->get_absolute_path($item);
		if (
			strpos($path_in_filesystem, CACHE) !== 0 ||
			!is_file($path_in_filesystem)
		) {
			return false;
		}
		$cache = file_get_contents($path_in_filesystem, FILE_BINARY);
		$cache = @_json_decode($cache);
		if ($cache !== false) {
			return $cache;
		}
		unlink($path_in_filesystem);
		return false;
	}
	/**
	 * @inheritdoc
	 */
	function set ($item, $data) {
		$path_in_filesystem = $this->get_absolute_path($item);
		if (strpos($path_in_filesystem, CACHE) !== 0) {
			return false;
		}
		$data = @_json_encode($data);
		if (mb_strpos($item, '/') !== false) {
			$path = mb_substr($item, 0, mb_strrpos($item, '/'));
			if (!is_dir(CACHE."/$path")) {
				@mkdir(CACHE."/$path", 0770, true);
			}
			unset($path);
		}
		if (!file_exists($path_in_filesystem) || is_writable($path_in_filesystem)) {
			return file_put_contents($path_in_filesystem, $data, LOCK_EX | FILE_BINARY);
		}
		trigger_error("File $path_in_filesystem not available for writing", E_USER_WARNING);
		return false;
	}
	/**
	 * @inheritdoc
	 */
	function del ($item) {
		$path_in_filesystem = $this->get_absolute_path($item);
		if (strpos($path_in_filesystem, CACHE) !== 0) {
			return false;
		}
		if (!file_exists($path_in_filesystem)) {
			return true;
		}
		if (is_dir($path_in_filesystem)) {
			/**
			 * Rename to random name in order to immediately invalidate nested elements, actual deletion done right after this
			 */
			$random_id = uniqid();
			$new_path  = $path_in_filesystem.$random_id;
			rename($path_in_filesystem, $new_path);
			/**
			 * Speed-up of files deletion
			 */
			get_files_list(
				$new_path,
				false,
				'f',
				true,
				true,
				false,
				true,
				false,
				function ($file) {
					if (is_writable($file)) {
						@unlink($file);
					}
				}
			);
			$files = get_files_list($new_path, false, 'fd');
			foreach ($files as $file) {
				$this->del($item.$random_id."/$file");
			}
			unset($files, $file);
			return @rmdir($new_path);
		}
		return @unlink($path_in_filesystem);
	}
	/**
	 * @inheritdoc
	 */
	function clean () {
		$ok         = true;
		$dirs_to_rm = [];
		/**
		 * Remove root files and rename root directories for instant cache cleaning
		 */
		$uniqid = uniqid();
		get_files_list(
			CACHE,
			false,
			'fd',
			true,
			false,
			false,
			false,
			true,
			function ($item) use (&$ok, &$dirs_to_rm, $uniqid) {
				if (is_writable($item)) {
					if (is_dir($item)) {
						rename($item, "$item$uniqid");
						$dirs_to_rm[] = "$item$uniqid";
					} else {
						@unlink($item);
					}
				} else {
					$ok = false;
				}
			}
		);
		/**
		 * Then remove all renamed directories
		 */
		foreach ($dirs_to_rm as $dir) {
			get_files_list(
				$dir,
				false,
				'fd',
				true,
				true,
				false,
				false,
				true,
				function ($item) use (&$ok) {
					if (is_writable($item)) {
						is_dir($item) ? @rmdir($item) : @unlink($item);
					} else {
						$ok = false;
					}
				}
			);
			@rmdir($dir);
		}
		return $ok;
	}
}
