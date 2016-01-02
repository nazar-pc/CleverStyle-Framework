<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Storage;
class Local extends _Abstract {
	/**
	 * @inheritdoc
	 */
	function __construct ($base_url, $host, $user = '', $password = '') {
		$this->base_url  = url_by_source(PUBLIC_STORAGE);
		$this->connected = true;
	}
	/**
	 * @inheritdoc
	 */
	function get_files_list ($dir, $mask = false, $mode = 'f', $prefix_path = false, $subfolders = false, $sort = false, $exclusion = false, $system_files = false, $apply = null, $limit = null) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function file ($filename, $flags = null) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function file_get_contents ($filename, $flags = null) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function file_put_contents ($filename, $data, $flags = null) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function copy ($source, $dest) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$arguments[1] = $this->absolute_path($arguments[1]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function unlink ($filename) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function file_exists ($filename) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function move_uploaded_file ($filename, $destination) {
		$arguments    = func_get_args();
		$arguments[1] = $this->absolute_path($arguments[1]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function rename ($oldname, $newname) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$arguments[1] = $this->absolute_path($arguments[1]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function mkdir ($pathname, $mode = 0777, $recursive = false) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function rmdir ($dirname) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function is_file ($filename) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function is_dir ($filename) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function url_by_source ($source) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	/**
	 * @inheritdoc
	 */
	function source_by_url ($url) {
		$arguments    = func_get_args();
		$arguments[0] = $this->absolute_path($arguments[0]);
		$return       = call_user_func_array(__FUNCTION__, $arguments);
		return $return;
	}
	protected function absolute_path ($path) {
		// If not absolute path -
		return preg_match('#^(([a-z]+:)?//|/)#i', $path) ? $path : PUBLIC_STORAGE.'/'.ltrim($path);
	}
}
