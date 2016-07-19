<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\App;
use
	cs\ExitException,
	cs\Page,
	cs\Response;

trait Static_files {
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected function serve_static_files ($Request) {
		$path = explode('?', $Request->path)[0];
		if (strlen($path) < 2) {
			return;
		}
		$path = realpath(DIR.$path);
		if (
			strpos($path, DIR) !== 0 ||
			strpos(basename($path), '.') === 0
		) {
			return;
		}
		$Response  = Response::instance();
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
			$Response->header('Content-Type', '');
			$this->serve_static_file($Response, $path);
		}
		/**
		 * Uploaded files
		 */
		if (strpos($path, '/storage/public') === 0) {
			$Response->header('X-Frame-Options', 'DENY');
			$Response->header('Content-Type', 'application/octet-stream');
			$this->serve_static_file($Response, $path);
		}
		/**
		 * System, modules and themes includes
		 */
		if (preg_match('#^/((modules/\w+/)?includes|themes/\w+)/.+#', $path)) {
			$Response->header('Content-Type', '');
			$this->serve_static_file($Response, $path);
		}
		throw new ExitException(404);
	}
	/**
	 * @param Response $Response
	 * @param string   $path
	 *
	 * @throws ExitException
	 */
	protected function serve_static_file ($Response, $path) {
		Page::instance()->interface = false;
		$Response->header('Cache-Control', 'max-age=2592000, public');
		$Response->body_stream = fopen(DIR.$path, 'rb');
		throw new ExitException;
	}
}
