<?php
namespace cs\Cache;
/**
 * Provides cache functionality based on file system structure.
 * Require base configuration option $Core->config('cache_size') with maximum allowed cache size in MB, 0 means without limitation (is not recomended)
 */
class FileSystem extends _Abstract {
	protected	$cache_size,
				$size			= null;

	function __construct () {
		global $Core;
		$this->cache_size = $Core->config('cache_size')*1048576;
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
		if (strpos($item, '/') !== false) {
			$subitems                       = explode('/', trim($item, "\n/"));
			$subitems[count($subitems) - 1] = trim($subitems[count($subitems) - 1]);
			$last = count($subitems) - 1;
			$path = [];
			foreach ($subitems as $i => $subitem) {
				if ($i == $last) {
					break;
				}
				$path[] = $subitem;
				if (!is_dir(CACHE.'/'.implode('/', $path))) {
					@mkdir(CACHE.'/'.implode('/', $path), 0770);
				}
			}
			unset($subitems, $last, $path, $i, $subitem);
		}
		if (!file_exists(CACHE.'/'.$item) || is_writable(CACHE.'/'.$item)) {
			if ($this->cache_size > 0) {
				$dsize = strlen($data);
				if (file_exists(CACHE.'/'.$item)) {
					$dsize -= filesize(CACHE.'/'.$item);
				}
				if ($dsize > $this->cache_size) {
					return false;
				}
				if ($this->size === null && file_exists(CACHE.'/size')) {
					$size = filesize(CACHE.'/size');
				}
				$size_file = fopen(CACHE.'/size', 'c+b');
				flock($size_file, LOCK_EX);
				if (isset($size) && $this->size === null) {
					$this->size = (int)fread($size_file, $size);
				} elseif ($this->size === null) {
					$this->size = 0;
				}
				unset($size);
				$this->size += $dsize;
				if ($this->size > $this->cache_size) {
					$cache_list = get_files_list(CACHE, false, 'f', true, true, 'date|desc');
					foreach ($cache_list as $file) {
						$this->size -= filesize($file);
						unlink($file);
						$disk_size = $this->cache_size * 2 / 3;
						if ($this->size <= $disk_size) {
							break;
						}
					}
					unset($cache_list, $file);
				}
				if (($return = file_put_contents(CACHE.'/'.$item, $data, LOCK_EX | FILE_BINARY)) !== false) {
					ftruncate($size_file, 0);
					fseek($size_file, 0);
					fwrite($size_file, $this->size > 0 ? $this->size : 0);
				} else {
					$this->size -= $dsize;
				}
				flock($size_file, LOCK_UN);
				fclose($size_file);
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
				$files = get_files_list(CACHE.'/'.$item, false, 'fd');
				foreach ($files as $file) {
					$this->del($item.'/'.$file, false);
				}
				unset($files, $file);
				return rmdir(CACHE.'/'.$item);
			}
			if ($this->cache_size > 0) {
				if ($this->size === null && file_exists(CACHE.'/size')) {
					$size = filesize(CACHE.'/size');
				}
				$size_file = fopen(CACHE.'/size', 'c+b');
				flock($size_file, LOCK_EX);
				if (isset($size) && $this->size === null) {
					$this->size .= (int)fread($size_file, $size);
				}
				$this->size -= filesize(CACHE.'/'.$item);
				if (unlink(CACHE.'/'.$item)) {
					ftruncate($size_file, 0);
					fseek($size_file, 0);
					fwrite($size_file, $this->size > 0 ? $this->size : 0);
				}
				flock($size_file, LOCK_UN);
				fclose($size_file);
			} else {
				unlink(CACHE.'/'.$item);
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
		$ok = true;
		$list = get_files_list(CACHE, false, 'fd', true, true, 'name|desc');
		foreach ($list as $item) {
			if (is_writable($item)) {
				is_dir($item) ? @rmdir($item) : @unlink($item);
			} else {
				$ok = false;
			}
		}
		unset($list, $item);
		return $ok;
	}
}