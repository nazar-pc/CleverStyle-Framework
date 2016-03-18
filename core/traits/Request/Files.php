<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;

trait Files {
	/**
	 * Normalized files array
	 *
	 * Each file item can be either single file or array of files (in contrast with native PHP arrays where each field like `name` become an array) with keys
	 * `name`, `type`, `size`, `tmp_name`, `stream` and `error`
	 *
	 * `name`, `type`, `size` and `error` keys are similar to native PHP fields in `$_FILES`; `tmp_name` might not be temporary file, but file descriptor
	 * wrapper like `php://fd/1` and `stream` is resource like obtained with `fopen('/tmp/xyz')`
	 *
	 * @var array[]
	 */
	public $files;
	/**
	 * @param array[] $files Typically `$_FILES`; might be like native PHP array `$_FILES` or normalized; each file item MUST contain keys `name`, `type`,
	 *                       `size`, `error` and at least one of `tmp_name` or `stream`
	 */
	function init_files ($files = []) {
		$this->files = $this->init_files_internal($files);
	}
	/**
	 * @param array[] $files
	 *
	 * @return array[]
	 */
	protected function init_files_internal ($files) {
		if (!isset($files['name'])) {
			foreach ($files as $field => &$file) {
				$file = $this->init_files_internal($file);
			}
			return $files;
		}
		if (is_array($files['name'])) {
			$result = [];
			foreach (array_keys($files['name']) as $index) {
				$result[] = $this->normalize_file(
					[
						'name'     => $files['name'][$index],
						'type'     => $files['type'][$index],
						'size'     => $files['size'][$index],
						'tmp_name' => @$files['tmp_name'][$index] ?: null,
						'stream'   => @$files['stream'][$index] ?: null,
						'error'    => $files['error'][$index]
					]
				);
			}
			return $result;
		} else {
			return $this->normalize_file($files);
		}
	}
	/**
	 * @param array $file
	 *
	 * @return array
	 */
	protected function normalize_file ($file) {
		$file += [
			'tmp_name' => null,
			'stream'   => null
		];
		if (isset($file['tmp_name']) && $file['stream'] === null) {
			$file['stream'] = fopen($file['tmp_name'], 'br');
		}
		if (isset($file['stream']) && $file['tmp_name'] === null) {
			$file['tmp_name'] = "php://fd/$file[stream]";
		}
		if ($file['tmp_name'] === null && $file['stream'] === null) {
			$file['error'] = UPLOAD_ERR_NO_FILE;
		}
		return $file;
	}
}
