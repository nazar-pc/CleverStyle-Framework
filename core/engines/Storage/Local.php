<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\Storage;
class Local extends _Abstract {
	/**
	 * Connecting to the Storage
	 *
	 * @param string	$base_url
	 * @param string	$host
	 * @param string	$user
	 * @param string	$password
	 */
	function __construct ($base_url, $host, $user = '', $password = '') {
		$this->base_url = url_by_source(STORAGE);
		$this->connected = true;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * Tells whether the filename is a regular file
	 *
	 * @see is_file()
	 *
	 * @param string	$filename
	 *
	 * @return bool
	 */
	function is_file ($filename) {
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
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
		$cwd	= getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
}
