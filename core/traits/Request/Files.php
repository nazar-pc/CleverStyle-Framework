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
	 * @var array
	 */
	public $files;
	/**
	 * @param array $files Typically `$_FILES`; might be like native PHP array `$_FILES` or normalized; each file item MUST contain keys `name`, `type`, `size`,
	 *                     `error` and at least one of `tmp_name` or `stream`
	 */
	function init_files ($files = []) {
		foreach ($files as $field => $file) {
			if (is_array($file['name'])) {
				foreach (array_keys($file['name']) as $index) {
					$this->files[$field][] = $this->normalize_file(
						[
							'name'     => $file['name'][$index],
							'type'     => $file['type'][$index],
							'size'     => $file['size'][$index],
							'tmp_name' => $file['tmp_name'][$index],
							'error'    => $file['error'][$index]
						]
					);
				}
			} else {
				$this->files[$field] = $this->normalize_file($file);
			}
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
