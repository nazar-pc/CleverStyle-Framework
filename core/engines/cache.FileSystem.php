<?php
/**
 * Provides cache functionality based on file system structure.
 * Require configuration variable $CACHE_SIZE with maximum allowed cache size in MB, 0 means without limitation (is not recomended)
 */
class FileSystem extends CacheAbstract {
	protected	$cache_size,
				$size			= null;

	function __construct () {
		global $CACHE_SIZE;
		$this->cache_size = $CACHE_SIZE*1048576;
	}
	/**
	 * @param string $item
	 *
	 * @return bool|mixed
	 */
	function get ($item) {
		if (DS != '/') {
			$item = str_replace('/', DS, $item);
		}
		if (_is_file(CACHE.DS.$item) && _is_readable(CACHE.DS.$item) && $cache = _file_get_contents(CACHE.DS.$item, FILE_BINARY)) {
			if (($cache = @_json_decode($cache)) !== false) {
				return $cache;
			} else {
				_unlink(CACHE.DS.$item);
				return false;
			}
		}
		return false;
	}
	/**
	 * @param string $item
	 * @param mixed  $data
	 *
	 * @return bool
	 */
	function set ($item, $data) {
		$data = @_json_encode($data);
		if (strpos($item, '/') !== false) {
			$subitems                       = explode('/', trim($item, "\n/"));
			$subitems[count($subitems) - 1] = trim($subitems[count($subitems) - 1]);
			$item = str_replace('/', DS, $item);
			$last = count($subitems) - 1;
			$path = [];
			foreach ($subitems as $i => $subitem) {
				if ($i == $last) {
					break;
				}
				$path[] = $subitem;
				if (!_is_dir(CACHE.DS.implode(DS, $path))) {
					@_mkdir(CACHE.DS.implode(DS, $path), 0770);
				}
			}
			unset($subitems, $last, $path, $i, $subitem);
		}
		if (!_file_exists(CACHE.DS.$item) || _is_writable(CACHE.DS.$item)) {
			if ($this->cache_size > 0) {
				$dsize = strlen($data);
				if (_file_exists(CACHE.DS.$item)) {
					$dsize -= _filesize(CACHE.DS.$item);
				}
				if ($dsize > $this->cache_size) {
					return false;
				}
				if ($this->size === null && _file_exists(CACHE.DS.'size')) {
					$size = _filesize(CACHE.DS.'size');
				}
				$size_file = _fopen(CACHE.DS.'size', 'c+b');
				flock($size_file, LOCK_EX);
				if (isset($size) && $this->size === null) {
					$this->size = (int)fread($size_file, $size);
				} elseif ($this->size === null) {
					$this->size = 0;
				}
				unset($size);
				$this->size += $dsize;
				if ($this->size > $this->cache_size) {
					$cache_list = get_list(CACHE, false, 'f', true, true, 'date|desc');
					foreach ($cache_list as $file) {
						$this->size -= _filesize($file);
						_unlink($file);
						$disk_size = $this->cache_size * 2 / 3;
						if ($this->size <= $disk_size) {
							break;
						}
					}
					unset($cache_list, $file);
				}
				if (($return = _file_put_contents(CACHE.DS.$item, $data, LOCK_EX | FILE_BINARY)) !== false) {
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
				return _file_put_contents(CACHE.DS.$item, $data, LOCK_EX | FILE_BINARY);
			}
		} else {
			global $L;
			trigger_error($L->file.' '.CACHE.DS.$item.' '.$L->not_writable, E_USER_WARNING);
			return false;
		}
	}
	/**
	 * @param string $item
	 *
	 * @return bool
	 */
	function del ($item) {
		return $this->del_internal($item);
	}
	/**
	 * @param string     $item
	 * @param bool       $process_mirrors
	 *
	 * @return bool
	 */
	protected function del_internal ($item, $process_mirrors = true) {
		if (empty($item) || $item == '/') {
			return false;
		}
		global $User;
		if ($process_mirrors && is_object($User) && !$User->is('system')) {
			global $Core;
			$Core->api_request('System/admin/cache/del', ['item' => $item]);
		}
		if (DS != '/') {
			$item = str_replace('/', DS, $item);
		}
		if (_is_writable(CACHE.DS.$item)) {
			if (_is_dir(CACHE.DS.$item)) {
				$files = get_list(CACHE.DS.$item, false, 'fd');
				foreach ($files as $file) {
					$this->del($item.'/'.$file, false);
				}
				unset($files, $file);
				return _rmdir(CACHE.DS.$item);
			}
			if ($this->cache_size > 0) {
				if ($this->size === null && _file_exists(CACHE.DS.'size')) {
					$size = _filesize(CACHE.DS.'size');
				}
				$size_file = _fopen(CACHE.DS.'size', 'c+b');
				flock($size_file, LOCK_EX);
				if (isset($size) && $this->size === null) {
					$this->size .= (int)fread($size_file, $size);
				}
				$this->size -= _filesize(CACHE.DS.$item);
				if (_unlink(CACHE.DS.$item)) {
					ftruncate($size_file, 0);
					fseek($size_file, 0);
					fwrite($size_file, $this->size > 0 ? $this->size : 0);
				}
				flock($size_file, LOCK_UN);
				fclose($size_file);
			} else {
				_unlink(CACHE.DS.$item);
			}
		} elseif (_file_exists(CACHE.DS.$item)) {
			return false;
		}
		return true;
	}
	/**
	 * @return bool
	 */
	function clean () {
		$ok = true;
		$list = get_list(CACHE, false, 'fd', true, true, 'name|desc');
		foreach ($list as $item) {
			if (_is_writable($item)) {
				_is_dir($item) ? @_rmdir($item) : @_unlink($item);
			} else {
				$ok = false;
			}
		}
		unset($list, $item);
		return $ok;
	}
}