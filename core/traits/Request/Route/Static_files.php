<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request\Route;
use
	cs\ExitException,
	cs\Response;

trait Static_files {
	/**
	 * @throws ExitException
	 */
	protected function serve_static_files () {
		$path = explode('?', $this->path)[0];
		if (
			$this->method != 'GET' ||
			!in_array(PHP_SAPI, ['cli', 'cli-server']) ||
			strlen($path) < 2
		) {
			return;
		}
		$path = realpath(DIR.$path);
		if (
			strpos($path, DIR) !== 0 ||
			strpos(basename($path), '.') === 0
		) {
			return;
		}
		$path      = substr($path, strlen(DIR));
		$extension = file_extension($path);
		if ($extension == 'php') {
			throw new ExitException(404);
		}
		/**
		 * Public cache
		 */
		if (strpos($path, '/storage/pcache') === 0) {
			if (!in_array($extension, ['css', 'js', 'html'])) {
				throw new ExitException(403);
			}
			$this->serve_static_file($path);
		}
		/**
		 * Uploaded files
		 */
		if (strpos($path, '/storage/public') === 0) {
			$headers = [
				'x-frame-options' => 'DENY',
				'content-type'    => 'application/octet-stream'
			];
			$this->serve_static_file($path, $headers);
		}
		/**
		 * System, modules and themes includes
		 */
		if (preg_match('#^/((modules/\w+/)?includes|themes/\w+)/.+#', $path)) {
			$this->serve_static_file($path);
		}
		throw new ExitException(404);
	}
	/**
	 * @param string   $path
	 * @param string[] $headers
	 *
	 * @throws ExitException
	 */
	protected function serve_static_file ($path, $headers = []) {
		$headers += ['cache-control' => 'max-age=2592000, public'];
		Response::instance()->init('', fopen(DIR.$path, 'rb'), $headers);
		throw new ExitException;
	}
}
