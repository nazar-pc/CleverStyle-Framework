<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Storage;
use			cs\Config;
class HTTP extends _Abstract {
	protected	$host,
				$user,
				$password;
	/**
	 * Connecting to the Storage
	 *
	 * @param string	$base_url
	 * @param string	$host
	 * @param string	$user
	 * @param string	$password
	 */
	function __construct ($base_url, $host, $user = '', $password = '') {
		$host				= explode(':', $host);
		$this->host			= $host;
		$this->user			= $user;
		$this->password		= $password;
		$this->base_url		= $base_url;
		$result				= $this->request(['function' => 'test']);
		$this->connected	= $result[1] == 'OK';
	}
	/**
	 * @param array			$data Key => value array
	 *
	 * @return array|bool	Return array(headers, body)
	 */
	protected function request ($data) {
		$socket = fsockopen($this->host[0], isset($this->host[1]) ? $this->host[1] : 80, $errno, $errstr);
		if(!is_resource($socket)) {
			trigger_error('#'.$errno.' '.$errstr, E_USER_WARNING);
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
	 * Function for getting content of a directory
	 *
	 * @see get_files_list()
	 *
	 * @param	string		$dir
	 * @param	bool|string	$mask
	 * @param	string		$mode
	 * @param	bool|string	$prefix_path
	 * @param	bool		$subfolders
	 * @param	bool		$sort
	 * @param	bool|string	$exclusion
	 * @param	bool		$system_files
	 * @param	\Closure	$apply
	 * @param	int|null	$limit
	 *
	 * @return	array|bool
	 */
	function get_files_list ($dir, $mask = false, $mode = 'f', $prefix_path = false, $subfolders = false, $sort = false, $exclusion = false, $system_files = false, $apply = null, $limit = null) {
		$return = _json_decode(
			$this->request([
				'function'		=> __FUNCTION__,
				'dir'			=> $dir,
				'mask'			=> $mask,
				'mode'			=> $mode,
				'prefix_path'	=> $prefix_path,
				'subfolders'	=> $subfolders,
				'sort'			=> $sort,
				'exclusion'		=> $exclusion,
				'system_files'	=> $system_files,
				'limit'			=> $limit
			])[1]
		);
		if ($apply instanceof \Closure && $return) {
			foreach ($return as $r) {
				$apply($r);
			}
			return [];
		} else {
			return $return;
		}
	}
	/**
	 * Reads entire file into an array
	 *
	 * @see file()
	 *
	 * @param string		$filename
	 * @param int			$flags
	 *
	 * @return array|bool
	 */
	function file ($filename, $flags = null) {
		return _json_decode(
			$this->request([
				'function'	=> __FUNCTION__,
				'filename'	=> $filename,
				'flags'		=> $flags
			])[1]
		);
	}
	/**
	 * Reads entire file into a string
	 *
	 * @see file_get_contents()
	 *
	 * @param string	$filename
	 * @param int		$flags
	 *
	 * @return bool|string
	 */
	function file_get_contents ($filename, $flags = null) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'filename'	=> $filename,
			'flags'		=> $flags]
		)[1];
	}
	/**
	 * Write a string to a file
	 *
	 * @see file_put_contents()
	 *
	 * @param string	$filename
	 * @param string	$data
	 * @param int		$flags
	 *
	 * @return bool|int
	 */
	function file_put_contents ($filename, $data, $flags = null) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'filename'	=> $filename,
			'data'		=> $data,
			'flags'		=> $flags
		])[1];
	}
	/**
	 * Copies file
	 *
	 * @see copy()
	 *
	 * @param string	$source
	 * @param string	$dest
	 *
	 * @return bool
	 */
	function copy ($source, $dest) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'source'	=> $source,
			'dest'		=> $dest
		])[1];
	}
	/**
	 * Deletes a file
	 *
	 * @see unlink()
	 *
	 * @param string	$filename
	 *
	 * @return bool
	 */
	function unlink ($filename) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'filename'	=> $filename
		])[1];
	}
	/**
	 * Checks whether a file or directory exists
	 *
	 * @see file_exists()
	 *
	 * @param string	$filename
	 *
	 * @return bool
	 */
	function file_exists ($filename) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'filename'	=> $filename
		])[1];
	}
	/**
	 * Moves an uploaded file to a new location
	 *
	 * @abstract
	 *
	 * @see move_uploaded_file()
	 *
	 * @param string	$filename
	 * @param string	$destination
	 *
	 * @return bool
	 */
	function move_uploaded_file ($filename, $destination) {
		$temp = md5(uniqid(microtime(true)));
		while (file_exists(TEMP.'/'.$temp)) {
			$temp = md5(uniqid(microtime(true)));
		}
		time_limit_pause();
		if (move_uploaded_file($filename, TEMP.'/'.$temp) === false) {
			time_limit_pause(false);
			return false;
		}
		time_limit_pause(false);
		return $this->request([
			'function'		=> __FUNCTION__,
			'filename'		=> Config::instance()->base_url()."/$temp",
			'destination'	=> $destination
		])[1] && unlink(TEMP.'/'.$temp);
	}
	/**
	 * Renames a file or directory
	 *
	 * @see rename()
	 *
	 * @param string	$oldname
	 * @param string	$newname
	 *
	 * @return bool
	 */
	function rename ($oldname, $newname) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'oldname'	=> $oldname,
			'newname'	=> $newname
		])[1];
	}
	/**
	 * Attempts to create the directory specified by pathname.
	 *
	 * @see mkdir()
	 *
	 * @param string	$pathname
	 * @param int		$mode
	 * @param bool		$recursive
	 *
	 * @return bool
	 */
	function mkdir ($pathname, $mode = 0777, $recursive = false) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'pathname'	=> $pathname
		])[1];
	}
	/**
	 * Removes directory
	 *
	 * @see rmdir()
	 *
	 * @param string	$dirname
	 *
	 * @return bool
	 */
	function rmdir ($dirname) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'dirname'	=> $dirname
		])[1];
	}
	/**
	 * Tells whether the filename is a regular file
	 *
	 * @abstract
	 *
	 * @see is_file()
	 *
	 * @param string	$filename
	 *
	 * @return bool
	 */
	function is_file ($filename) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'filename'	=> $filename
		])[1];
	}
	/**
	 * Tells whether the filename is a directory
	 *
	 * @see is_dir()
	 *
	 * @param string	$filename
	 *
	 * @return bool
	 */
	function is_dir ($filename) {
		return $this->request([
			'function'	=> __FUNCTION__,
			'filename'	=> $filename
		])[1];
	}
	/**
	 * Get file url by it's destination in file system
	 *
	 * @see url_by_source()
	 *
	 * @param string		$source
	 *
	 * @return bool|string
	 */
	function url_by_source ($source) {
		if ($this->file_exists($source)) {
			return $this->base_url.'/'.$source;
		}
		return false;
	}
	/**
	 * Get file destination in file system by it's url
	 *
	 * @see source_by_url()
	 *
	 * @param string		$url
	 *
	 * @return bool|string
	 */
	function source_by_url ($url) {
		if (mb_strpos($url, $this->base_url) === 0) {
			return str_replace($this->base_url.'/', '', $url);
		}
		return false;
	}
}