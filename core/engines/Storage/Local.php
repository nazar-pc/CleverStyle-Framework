<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Storage;
class Local extends _Abstract {
	/**
	 * @inheritdoc
	 */
	function __construct ($base_url, $host, $user = '', $password = '') {
		$this->base_url  = url_by_source(STORAGE);
		$this->connected = true;
	}
	/**
	 * @inheritdoc
	 */
	function get_files_list ($dir, $mask = false, $mode = 'f', $prefix_path = false, $subfolders = false, $sort = false, $exclusion = false, $system_files = false, $apply = null, $limit = null) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function file ($filename, $flags = null) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function file_get_contents ($filename, $flags = null) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function file_put_contents ($filename, $data, $flags = null) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function copy ($source, $dest) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function unlink ($filename) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function file_exists ($filename) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function move_uploaded_file ($filename, $destination) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function rename ($oldname, $newname) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function mkdir ($pathname, $mode = 0777, $recursive = false) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function rmdir ($dirname) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function is_file ($filename) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function is_dir ($filename) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function url_by_source ($source) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function source_by_url ($url) {
		$cwd = getcwd();
		chdir(STORAGE);
		$return = call_user_func_array(__FUNCTION__, func_get_args());
		chdir($cwd);
		return $return;
	}
}
