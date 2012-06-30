<?php
namespace cs\storage;
class Local extends _Abstract {
	function __construct ($base_url, $host, $user = '', $password = '') {
		$this->base_url = url_by_source(STORAGE);
		$this->connected = true;
	}
	function get_list ($dir, $mask = false, $mode='f', $with_path = false, $subfolders = false, $sort = false, $exclusion = false) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function file_get_contents ($filename, $flags = 0) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function file_put_contents ($filename, $data, $flags = 0) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function copy ($source, $dest) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function unlink ($filename) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function file_exists ($filename) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function move_uploaded_file ($filename, $destination) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function rename ($oldname, $newname) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function mkdir ($pathname, $mode = 0777, $recursive = false) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function rmdir ($dirname) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function url_by_source ($source) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function source_by_url ($url) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function is_file ($filename) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
	function is_dir ($filename) {
		return call_user_func_array(__FUNCTION__, func_get_args());
	}
}