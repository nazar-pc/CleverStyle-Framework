<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Storage;
use
	cs\Config;

class Local extends _Abstract {
	/**
	 * @inheritdoc
	 */
	public function __construct ($base_url, $host, $user = '', $password = '') {
		$this->base_url  = $base_url ?: $this->url_by_source(PUBLIC_STORAGE);
		$this->connected = true;
	}
	/**
	 * @inheritdoc
	 */
	public function get_files_list (
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
	public function file ($filename, $flags = null) {
		return file($this->absolute_path($filename), $flags);
	}
	/**
	 * @inheritdoc
	 */
	public function file_get_contents ($filename, $flags = null) {
		return file_get_contents($this->absolute_path($filename), $flags);
	}
	/**
	 * @inheritdoc
	 */
	public function file_put_contents ($filename, $data, $flags = null) {
		return file_put_contents($this->absolute_path($filename), $data, $flags);
	}
	/**
	 * @inheritdoc
	 */
	public function copy ($source, $dest) {
		return copy($this->absolute_path($source), $this->absolute_path($dest));
	}
	/**
	 * @inheritdoc
	 */
	public function unlink ($filename) {
		return unlink($this->absolute_path($filename));
	}
	/**
	 * @inheritdoc
	 */
	public function file_exists ($filename) {
		return file_exists($this->absolute_path($filename));
	}
	/**
	 * @inheritdoc
	 */
	public function rename ($oldname, $newname) {
		return rename($this->absolute_path($oldname), $this->absolute_path($newname));
	}
	/**
	 * @inheritdoc
	 */
	public function mkdir ($pathname, $mode = 0777, $recursive = false) {
		/** @noinspection MkdirRaceConditionInspection */
		return mkdir($this->absolute_path($pathname), $mode, $recursive);
	}
	/**
	 * @inheritdoc
	 */
	public function rmdir ($dirname) {
		return rmdir($this->absolute_path($dirname));
	}
	/**
	 * @inheritdoc
	 */
	public function is_file ($filename) {
		return is_file($this->absolute_path($filename));
	}
	/**
	 * @inheritdoc
	 */
	public function is_dir ($filename) {
		return is_dir($this->absolute_path($filename));
	}
	/**
	 * @inheritdoc
	 */
	public function url_by_source ($source) {
		$source = $this->absolute_path($source);
		if (strpos($source, PUBLIC_STORAGE) === 0) {
			$source = Config::instance()->core_url().substr($source, strlen(DIR));
		}
		return str_replace('\\', '/', $source);
	}
	/**
	 * @inheritdoc
	 */
	public function source_by_url ($url) {
		$Config = Config::instance();
		if (strpos($url, $Config->core_url()) === 0) {
			$url = DIR.substr($url, strlen($Config->core_url()));
		}
		return $this->relative_path($url);
	}
	/**
	 * @param string $path
	 *
	 * @return string
	 */
	protected function absolute_path ($path) {
		return preg_match('#^(([a-z]+:)?//|/)#i', $path) ? $path : PUBLIC_STORAGE.'/'.ltrim($path);
	}
	/**
	 * @param string $path
	 *
	 * @return string
	 */
	protected function relative_path ($path) {
		return strpos($path, PUBLIC_STORAGE.'/') === 0 ? $path : substr($path, strlen(PUBLIC_STORAGE.'/'));
	}
}
