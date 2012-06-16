<?php
class HTTP extends StorageAbstract {
	protected	$host,
				$user,
				$password;
	//Создание подключения
	function __construct ($base_url, $host, $user = '', $password = '') {
		$host				= explode(':', $host);
		$this->host			= $host;
		$this->user			= $user;
		$this->password		= $password;
		$this->base_url		= $base_url;
		$result				= $this->request(['function' => 'test']);
		$this->connected	= $result[1] == 'OK';
	}
	//(массив_вида_ключ_значение)
	//Возвращает массив из двух елементов:
	//0 - заголовки, 1 - тело документа
	protected function request ($data) {
		$socket = fsockopen($this->host[0], isset($this->host[1]) ? $this->host[1] : 80, $errno, $errstr);
		if(!is_resource($socket)) {
			trigger_error('#'.$errno.' '.$errstr, E_WARNING);
			$this->connected = false;
			return false;
		}
		if (empty($data)) {
			return false;
		} else {
			$data['key'] = md5(_json_encode($data).$this->user.$this->password);
		}
		$data = 'data='.urlencode(json_encode($data)).'&domain='.DOMAIN;
		time_limit_pause();
		fwrite(
			$socket,
			"POST /Storage.php HTTP/1.1\r\n".
			'Host: '.implode(':', $this->host)."\r\n".
			"Content-type: application/x-www-form-urlencoded\r\n".
			"Content-length:".strlen($data)."\r\n".
			"Accept:*/*\r\n".
			"User-agent: CleverStyle CMS".
			/*'Authorization: Basic '.base64_encode($this->user.':'.$this->password).*/"\r\n\r\n".
			$data."\r\n\r\n"
		);
		$return = explode("\r\n\r\n", stream_get_contents($socket), 2);
		time_limit_pause(false);
		fclose($socket);
		return $return;
	}
	function get_list ($dir, $mask = false, $mode='f', $with_path = false, $subfolders = false, $sort = false, $exclusion = false) {
		$result = $this->request(
			[
				'function'		=> __FUNCTION__,
				'dir'			=> $dir,
				'mask'			=> $mask,
				'mode'			=> $mode,
				'with_path'		=> $with_path,
				'subfolders'	=> $subfolders,
				'sort'			=> $sort,
				'exclusion'		=> $exclusion
			]
		);
		return _json_decode($result[1]);
	}
	function file_get_contents ($filename, $flags = 0) {
		$result = $this->request(['function' => __FUNCTION__, 'filename' => $filename, 'flags' => $flags]);
		return $result[1];
	}
	function file_put_contents ($filename, $data, $flags = 0) {
		$result = $this->request(['function' => __FUNCTION__, 'filename' => $filename, 'data' => $data, 'flags' => $flags]);
		return $result[1];
	}
	function copy ($source, $dest) {
		if (($source == _realpath($source)) === false) {
			return false;
		}
		$temp = md5(uniqid(microtime(true)));
		while (_file_exists(TEMP.DS.$temp)) {
			$temp = md5(uniqid(microtime(true)));
		}
		time_limit_pause();
		if (!_copy($source, TEMP.DS.$temp)) {
			time_limit_pause(false);
			return false;
		}
		time_limit_pause(false);
		global $Config;
		$source = $Config->server['base_url'].'/'.$temp;
		$result = $this->request(['function' => __FUNCTION__, 'source' => $source, 'dest' => $dest, 'http' => $temp]);
		_unlink(TEMP.DS.$temp);
		return (bool)$result[1];
	}
	function unlink ($filename) {
		$result = $this->request(['function' => __FUNCTION__, 'filename' => $filename]);
		return (bool)$result[1];
	}
	function file_exists ($filename) {
		$result = $this->request(['function' => __FUNCTION__, 'filename' => $filename]);
		return (bool)$result[1];
	}
	function move_uploaded_file ($filename, $destination) {
		$temp = md5(uniqid(microtime(true)));
		while (_file_exists(TEMP.DS.$temp)) {
			$temp = md5(uniqid(microtime(true)));
		}
		time_limit_pause();
		if (_move_uploaded_file($filename, TEMP.DS.$temp) === false) {
			time_limit_pause(false);
			return false;
		}
		time_limit_pause(false);
		global $Config;
		$result = $this->request(['function' => __FUNCTION__, 'filename' => $Config->server['base_url'].'/'.$temp, 'destination' => $destination]);
		_unlink(TEMP.DS.$temp);
		return (bool)$result[1];
	}
	function rename ($oldname, $newname) {
		if (($oldname == _realpath($oldname)) === false) {
			return false;
		}
		$temp = md5(uniqid(microtime(true)));
		while (_file_exists(TEMP.DS.$temp)) {
			$temp = md5(uniqid(microtime(true)));
		}
		time_limit_pause();
		if (!_copy($oldname, TEMP.DS.$temp)) {
			time_limit_pause(false);
			return false;
		}
		time_limit_pause(false);
		global $Config;
		$oldname_x	= $oldname;
		$oldname	= $Config->server['base_url'].'/'.$temp;
		$result		= $this->request(['function' => __FUNCTION__, 'oldname' => $oldname, 'newname' => $newname, 'http' => $temp]);
		_unlink(TEMP.DS.$temp);
		if ($result[1]) {
			_unlink($oldname_x);
		}
		return (bool)$result[1];
	}
	function mkdir ($pathname, $mode = 0777, $recursive = false) {
		$result = $this->request(['function' => __FUNCTION__, 'pathname' => $pathname]);
		return (bool)$result[1];
	}
	function rmdir ($dirname) {
		$result = $this->request(['function' => __FUNCTION__, 'dirname' => $dirname]);
		return (bool)$result[1];
	}
	function is_file ($filename) {
		$result = $this->request(['function' => __FUNCTION__, 'filename' => $filename]);
		return (bool)$result[1];
	}
	function is_dir ($filename) {
		$result = $this->request(['function' => __FUNCTION__, 'filename' => $filename]);
		return (bool)$result[1];
	}
	function url_by_source ($source) {
		if ($this->file_exists($source)) {
			return $this->base_url.'/'.$source;
		}
		return false;
	}
	function source_by_url ($url) {
		if (strpos($url, $this->base_url) === 0) {
			global $Config;
			if (is_object($Config)) {
				return str_replace($this->base_url.'/', '', $url);
			}
		}
		return false;
	}
}