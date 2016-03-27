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
	function get_files_list (
		$dir,
		$mask = false,
		$mode = 'f',
		$prefix_path = false,
		$subfolders = false,
		$sort = false,
		$exclusion = false,
		$system_files = false,
		$apply = null,
		$limit = null
	) {
		return get_files_list($this->absolute_path($dir), $mask, $mode, $prefix_path, $subfolders, $sort, $exclusion, $system_files, $apply, $limit);
	}
	/**
	 * @inheritdoc
	 */
	function file ($filename, $flags = null) {
		return file($this->absolute_path($filename), $flags);
	}
	/**
	 * @inheritdoc
	 */
	function file_get_contents ($filename, $flags = null) {
		return file_get_contents($this->absolute_path($filename), $flags);
	}
	/**
	 * @inheritdoc
	 */
	function file_put_contents ($filename, $data, $flags = null) {
		return file_put_contents($this->absolute_path($filename), $data, $flags);
	}
	/**
	 * @inheritdoc
	 */
	function copy ($source, $dest) {
		return copy($this->absolute_path($source), $this->absolute_path($dest));
	}
	/**
	 * @inheritdoc
	 */
	function unlink ($filename) {
		return unlink($this->absolute_path($filename));
	}
	/**
	 * @inheritdoc
	 */
	function file_exists ($filename) {
		return file_exists($this->absolute_path($filename));
	}
	/**
	 * @inheritdoc
	 */
	function move_uploaded_file ($filename, $destination) {
		return copy($filename, $this->absolute_path($destination));
	}
	/**
	 * @inheritdoc
	 */
	function rename ($oldname, $newname) {
		return rename($this->absolute_path($oldname), $this->absolute_path($newname));
	}
	/**
	 * @inheritdoc
	 */
	function mkdir ($pathname, $mode = 0777, $recursive = false) {
		/** @noinspection MkdirRaceConditionInspection */
		return mkdir($this->absolute_path($pathname), $mode, $recursive);
	}
	/**
	 * @inheritdoc
	 */
	function rmdir ($dirname) {
		return rmdir($this->absolute_path($dirname));
	}
	/**
	 * @inheritdoc
	 */
	function is_file ($filename) {
		return is_file($this->absolute_path($filename));
	}
	/**
	 * @inheritdoc
	 */
	function is_dir ($filename) {
		return is_dir($this->absolute_path($filename));
	}
	/**
	 * @inheritdoc
	 */
	function url_by_source ($source) {
		return url_by_source($this->absolute_path($source));
	}
	/**
	 * @inheritdoc
	 */
	function source_by_url ($url) {
		return $this->relative_path(source_by_url($url));
	}
	protected function absolute_path ($path) {
		return preg_match('#^(([a-z]+:)?//|/)#i', $path) ? $path : PUBLIC_STORAGE.'/'.ltrim($path);
	}
	protected function relative_path ($path) {
		return strpos($path, PUBLIC_STORAGE.'/') === 0 ? $path : substr($path, strlen(PUBLIC_STORAGE.'/'));
	}
}
