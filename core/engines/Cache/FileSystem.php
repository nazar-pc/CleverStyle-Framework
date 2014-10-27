<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Cache;
use			cs\Config,
			cs\Core,
			cs\Language;
/**
 * Provides cache functionality based on file system structure.
 * Require base configuration option Core::instance()->cache_size with maximum allowed cache size in MB, 0 means without limitation (is not recommended)
 */
class FileSystem extends _Abstract {
	protected	$cache_size;
	function __construct () {
		$this->cache_size = Core::instance()->cache_size * 1024 * 1024;
	}
	/**
	 * Like realpath() but works even if files does not exists
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	protected function get_absolute_path ($path) {
		$path = str_replace(['/', '\\'], '/', $path);
		$parts = array_filter(explode('/', $path), 'strlen');
		$absolutes = [];
		foreach ($parts as $part) {
			if ('.' == $part) continue;
			if ('..' == $part) {
				array_pop($absolutes);
			} else {
				$absolutes[] = $part;
			}
		}
		return '/'.implode('/', $absolutes);
	}
	/**
	 * Get item from cache
	 *
	 * @param string		$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool|mixed			Returns item on success of <b>false</b> on failure
	 */
	function get ($item) {
		$path_in_filesystem = $this->get_absolute_path(CACHE."/$item");
		if (strpos($path_in_filesystem, CACHE) !== 0) {
			return false;
		}
		if (is_file($path_in_filesystem) && is_readable($path_in_filesystem) && $cache = file_get_contents($path_in_filesystem, FILE_BINARY)) {
			if (($cache = @_json_decode($cache)) !== false) {
				return $cache;
			} else {
				unlink($path_in_filesystem);
				return false;
			}
		}
		return false;
	}
	/**
	 * Put or change data of cache item
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 * @param mixed		$data
	 *
	 * @return bool
	 */
	function set ($item, $data) {
		$path_in_filesystem = $this->get_absolute_path(CACHE."/$item");
		if (strpos($path_in_filesystem, CACHE) !== 0) {
			return false;
		}
		$data = @_json_encode($data);
		if (mb_strpos($item, '/') !== false) {
			$path	=mb_substr($item, 0, mb_strrpos($item, '/'));
			if (!is_dir(CACHE."/$path")) {
				@mkdir(CACHE."/$path", 0770, true);
			}
			unset($path);
		}
		if (!file_exists($path_in_filesystem) || is_writable($path_in_filesystem)) {
			if ($this->cache_size > 0) {
				$dsize				= strlen($data);
				if ($dsize > $this->cache_size) {
					return false;
				}
				if (file_exists($path_in_filesystem)) {
					$dsize -= filesize($path_in_filesystem);
				}
				$cache_size_file	= fopen(CACHE.'/size', 'c+b');
				$time				= microtime(true);
				while (!flock($cache_size_file, LOCK_EX | LOCK_NB)) {
					if ($time < microtime(true) - .5) {
						fclose($cache_size_file);
						return false;
					}
					usleep(1000);
				}
				unset($time);
				$cache_size	= (int)stream_get_contents($cache_size_file);
				$cache_size	+= $dsize;
				if ($cache_size > $this->cache_size) {
					$cache_list = get_files_list(CACHE, false, 'f', true, true, 'date|desc');
					foreach ($cache_list as $file) {
						$cache_size -= filesize($file);
						unlink($file);
						$disk_size = $this->cache_size * 2 / 3;
						if ($cache_size <= $disk_size * Config::instance()->core['update_ratio'] / 100) {
							break;
						}
					}
					unset($cache_list, $file);
				}
				if (($return = file_put_contents($path_in_filesystem, $data, LOCK_EX | FILE_BINARY)) !== false) {
					ftruncate($cache_size_file, 0);
					fseek($cache_size_file, 0);
					fwrite($cache_size_file, $cache_size > 0 ? $cache_size : 0);
				}
				flock($cache_size_file, LOCK_UN);
				fclose($cache_size_file);
				return $return;
			} else {
				return file_put_contents($path_in_filesystem, $data, LOCK_EX | FILE_BINARY);
			}
		} else {
			$L	= Language::instance();
			trigger_error("$L->file $path_in_filesystem $L->not_writable", E_USER_WARNING);
			return false;
		}
	}
	/**
	 * Delete item from cache
	 *
	 * @param string	$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool
	 */
	function del ($item) {
		$path_in_filesystem = $this->get_absolute_path(CACHE."/$item");
		if (strpos($path_in_filesystem, CACHE) !== 0) {
			return false;
		}
		if (is_writable($path_in_filesystem)) {
			if (is_dir($path_in_filesystem)) {
				/**
				 * Rename to random name in order to immediately invalidate nested elements, actual deletion done right after this
				 */
				$new_path	= $path_in_filesystem.uniqid();
				rename($path_in_filesystem, $new_path);
				/**
				 * Speed-up of files deletion
				 */
				if (!($this->cache_size > 0)) {
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
				}
				$files = get_files_list($new_path, false, 'fd');
				foreach ($files as $file) {
					$this->del($item."/$file", false);
				}
				unset($files, $file);
				return @rmdir($new_path);
			}
			if ($this->cache_size > 0) {
				$cache_size_file	= fopen(CACHE.'/size', 'c+b');
				$time				= microtime(true);
				while (!flock($cache_size_file, LOCK_EX | LOCK_NB)) {
					if ($time < microtime(true) - .5) {
						fclose($cache_size_file);
						return false;
					}
					usleep(1000);
				}
				unset($time);
				$cache_size	= (int)stream_get_contents($cache_size_file);
				$cache_size -= filesize($path_in_filesystem);
				if (@unlink($path_in_filesystem)) {
					ftruncate($cache_size_file, 0);
					fseek($cache_size_file, 0);
					fwrite($cache_size_file, $cache_size > 0 ? $cache_size : 0);
				}
				flock($cache_size_file, LOCK_UN);
				fclose($cache_size_file);
			} else {
				@unlink($path_in_filesystem);
			}
		} elseif (file_exists($path_in_filesystem)) {
			return false;
		}
		return true;
	}
	/**
	 * Clean cache by deleting all items
	 *
	 * @return bool
	 */
	function clean () {
		$ok			= true;
		$dirs_to_rm	= [];
		/**
		 * Remove root files and rename root directories for instant cache cleaning
		 */
		$uniqid		= uniqid();
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
						$dirs_to_rm[]	= "$item$uniqid";
					} else {
						@unlink($item);
					}
				} else {
					$ok = false;
				}
			}
		);
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
