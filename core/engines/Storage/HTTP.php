<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Storage;
use            cs\Config;

class HTTP extends _Abstract {
	protected $host;
	protected $user;
	protected $password;
	/**
	 * @inheritdoc
	 */
	function __construct ($base_url, $host, $user = '', $password = '') {
		$host            = explode(':', $host);
		$this->host      = $host;
		$this->user      = $user;
		$this->password  = $password;
		$this->base_url  = $base_url;
		$result          = $this->request(['function' => 'test']);
		$this->connected = $result[1] == 'OK';
	}
	/**
	 * @param array $data Key => value array
	 *
	 * @return array|bool    Return array(headers, body)
	 */
	protected function request ($data) {
		$socket = fsockopen($this->host[0], isset($this->host[1]) ? $this->host[1] : 80, $errno, $errstr);
		if (!is_resource($socket)) {
			trigger_error("#$errno $errstr", E_USER_WARNING);
			$this->connected = false;
			return false;
		}
		if (empty($data)) {
			return false;
		} else {
			$data['key'] = md5(_json_encode($data).$this->user.$this->password);
		}
		$data = 'data='.urlencode(json_encode($data));
		time_limit_pause();
		fwrite(
			$socket,
			"POST /Storage.php HTTP/1.1\r\n".
			'Host: '.implode(':', $this->host)."\r\n".
			"Content-Type: application/x-www-form-urlencoded\r\n".
			"Content-length:".strlen($data)."\r\n".
			"Accept:*/*\r\n".
			"User-agent: CleverStyle CMS\r\n\r\n".
			$data."\r\n\r\n"
		);
		$return = explode("\r\n\r\n", stream_get_contents($socket), 2);
		time_limit_pause(false);
		fclose($socket);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function get_files_list ($dir, $mask = false, $mode = 'f', $prefix_path = false, $subfolders = false, $sort = false, $exclusion = false, $system_files = false, $apply = null, $limit = null) {
		$return = _json_decode(
			$this->request([
				'function'     => __FUNCTION__,
				'dir'          => $dir,
				'mask'         => $mask,
				'mode'         => $mode,
				'prefix_path'  => $prefix_path,
				'subfolders'   => $subfolders,
				'sort'         => $sort,
				'exclusion'    => $exclusion,
				'system_files' => $system_files,
				'limit'        => $limit
			])[1]
		);
		if (is_callable($apply) && $return) {
			foreach ($return as $r) {
				$apply($r);
			}
			return [];
		} else {
			return $return;
		}
	}
	/**
	 * @inheritdoc
	 */
	function file ($filename, $flags = null) {
		return _json_decode(
			$this->request([
				'function' => __FUNCTION__,
				'filename' => $filename,
				'flags'    => $flags
			])[1]
		);
	}
	/**
	 * @inheritdoc
	 */
	function file_get_contents ($filename, $flags = null) {
		return $this->request([
				'function' => __FUNCTION__,
				'filename' => $filename,
				'flags'    => $flags]
		)[1];
	}
	/**
	 * @inheritdoc
	 */
	function file_put_contents ($filename, $data, $flags = null) {
		return $this->request([
			'function' => __FUNCTION__,
			'filename' => $filename,
			'data'     => $data,
			'flags'    => $flags
		])[1];
	}
	/**
	 * @inheritdoc
	 */
	function copy ($source, $dest) {
		return $this->request([
			'function' => __FUNCTION__,
			'source'   => $source,
			'dest'     => $dest
		])[1];
	}
	/**
	 * @inheritdoc
	 */
	function unlink ($filename) {
		return $this->request([
			'function' => __FUNCTION__,
			'filename' => $filename
		])[1];
	}
	/**
	 * @inheritdoc
	 */
	function file_exists ($filename) {
		return $this->request([
			'function' => __FUNCTION__,
			'filename' => $filename
		])[1];
	}
	/**
	 * @inheritdoc
	 */
	function move_uploaded_file ($filename, $destination) {
		$temp = md5(openssl_random_pseudo_bytes(1000));
		while (file_exists(TEMP."/$temp")) {
			$temp = md5(openssl_random_pseudo_bytes(1000));
		}
		time_limit_pause();
		if (move_uploaded_file($filename, TEMP."/$temp") === false) {
			time_limit_pause(false);
			return false;
		}
		time_limit_pause(false);
		return $this->request([
			'function'    => __FUNCTION__,
			'filename'    => Config::instance()->base_url()."/$temp",
			'destination' => $destination
		])[1] && unlink(TEMP."/$temp");
	}
	/**
	 * @inheritdoc
	 */
	function rename ($oldname, $newname) {
		return $this->request([
			'function' => __FUNCTION__,
			'oldname'  => $oldname,
			'newname'  => $newname
		])[1];
	}
	/**
	 * @inheritdoc
	 */
	function mkdir ($pathname, $mode = 0777, $recursive = false) {
		return $this->request([
			'function' => __FUNCTION__,
			'pathname' => $pathname
		])[1];
	}
	/**
	 * @inheritdoc
	 */
	function rmdir ($dirname) {
		return $this->request([
			'function' => __FUNCTION__,
			'dirname'  => $dirname
		])[1];
	}
	/**
	 * @inheritdoc
	 */
	function is_file ($filename) {
		return $this->request([
			'function' => __FUNCTION__,
			'filename' => $filename
		])[1];
	}
	/**
	 * @inheritdoc
	 */
	function is_dir ($filename) {
		return $this->request([
			'function' => __FUNCTION__,
			'filename' => $filename
		])[1];
	}
	/**
	 * @inheritdoc
	 */
	function url_by_source ($source) {
		if ($this->file_exists($source)) {
			return $this->base_url."/$source";
		}
		return false;
	}
	/**
	 * @inheritdoc
	 */
	function source_by_url ($url) {
		if (mb_strpos($url, $this->base_url) === 0) {
			return str_replace("$this->base_url/", '', $url);
		}
		return false;
	}
}
