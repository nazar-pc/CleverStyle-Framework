<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\Cache;
/**
 * Provides cache functionality based on file system structure.
 * Require base configuration option $Core->cache_size with maximum allowed cache size in MB, 0 means without limitation (is not recommended)
 */
class FileSystem extends _Abstract {
	protected	$cache_size;
	function __construct () {
		global $Core;
		$this->cache_size = $Core->cache_size*1048576;
	}
	/**
	 * Get item from cache
	 *
	 * @param string		$item	May contain "/" symbols for cache structure, for example users/<i>user_id</i>
	 *
	 * @return bool|mixed			Returns item on success of <b>false</b> on failure
	 */
	function get ($item) {
		if (is_file(CACHE.'/'.$item) && is_readable(CACHE.'/'.$item) && $cache = file_get_contents(CACHE.'/'.$item, FILE_BINARY)) {
			if (($cache = @_json_decode($cache)) !== false) {
				return $cache;
			} else {
				unlink(CACHE.'/'.$item);
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
		$data = @_json_encode($data);
		if (mb_strpos($item, '/') !== false) {
			$path	=mb_substr($item, 0, mb_strrpos($item, '/'));
			if (!is_dir(CACHE.'/'.$path)) {
				@mkdir(CACHE.'/'.$path, 0700, true);
			}
			unset($path);
		}
		if (!file_exists(CACHE.'/'.$item) || is_writable(CACHE.'/'.$item)) {
			if ($this->cache_size > 0) {
				$dsize				= strlen($data);
				if ($dsize > $this->cache_size) {
					return false;
				}
				if (file_exists(CACHE.'/'.$item)) {
					$dsize -= filesize(CACHE.'/'.$item);
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
					global $Config;
					foreach ($cache_list as $file) {
						$cache_size -= filesize($file);
						unlink($file);
						$disk_size = $this->cache_size * 2 / 3;
						if ($cache_size <= $disk_size * $Config->core['update_ratio'] / 100) {
							break;
						}
					}
					unset($cache_list, $file);
				}
				if (($return = file_put_contents(CACHE.'/'.$item, $data, LOCK_EX | FILE_BINARY)) !== false) {
					ftruncate($cache_size_file, 0);
					fseek($cache_size_file, 0);
					fwrite($cache_size_file, $cache_size > 0 ? $cache_size : 0);
				}
				flock($cache_size_file, LOCK_UN);
				fclose($cache_size_file);
				return $return;
			} else {
				return file_put_contents(CACHE.'/'.$item, $data, LOCK_EX | FILE_BINARY);
			}
		} else {
			global $L;
			trigger_error($L->file.' '.CACHE.'/'.$item.' '.$L->not_writable, E_USER_WARNING);
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
		if (is_writable(CACHE.'/'.$item)) {
			if (is_dir(CACHE.'/'.$item)) {
				/**
				 * Speed-up of files deletion
				 */
				if (!($this->cache_size > 0)) {
					get_files_list(
						CACHE.'/'.$item,
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
				$files = get_files_list(CACHE.'/'.$item, false, 'fd');
				foreach ($files as $file) {
					$this->del($item.'/'.$file, false);
				}
				unset($files, $file);
				return @rmdir(CACHE.'/'.$item);
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
				$cache_size -= filesize(CACHE.'/'.$item);
				if (@unlink(CACHE.'/'.$item)) {
					ftruncate($cache_size_file, 0);
					fseek($cache_size_file, 0);
					fwrite($cache_size_file, $cache_size > 0 ? $cache_size : 0);
				}
				flock($cache_size_file, LOCK_UN);
				fclose($cache_size_file);
			} else {
				@unlink(CACHE.'/'.$item);
			}
		} elseif (file_exists(CACHE.'/'.$item)) {
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
		$cache_old	= CACHE.'_old';
		rename(CACHE, $cache_old);
		get_files_list(
			$cache_old,
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
		unset($item);
		rmdir($cache_old);
		return $ok;
	}
}