<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page\Includes;
use
	cs\Config,
	cs\Language,
	cs\Page\Includes_processing;

trait Cache {
	/**
	 * Creates cached version of given HTML, JS and CSS files.
	 * Resulting files names consist of `$filename_prefix` and extension.
	 *
	 * @param string     $filename_prefix
	 * @param string[][] $includes Array of paths to files, may have keys: `css` and/or `js` and/or `html`
	 *
	 * @return array
	 */
	protected function cache_compressed_includes_files ($filename_prefix, $includes) {
		$local_includes = [];
		foreach ($includes as $extension => $files) {
			$content  = $this->cache_compressed_includes_files_single($extension, $filename_prefix, $files);
			$filename = "$filename_prefix.$extension";
			file_put_contents(PUBLIC_CACHE."/$filename", gzencode($content, 9), LOCK_EX | FILE_BINARY);
			$local_includes[$extension] = "storage/pcache/$filename?".substr(md5($content), 0, 5);
		}
		return $local_includes;
	}
	protected function cache_compressed_includes_files_single ($extension, $filename_prefix, $files) {
		$content = '';
		switch ($extension) {
			/**
			 * Insert external elements into resulting css file.
			 * It is needed, because those files will not be copied into new destination of resulting css file.
			 */
			case 'css':
				$callback = function ($content, $file) {
					return $content.Includes_processing::css(file_get_contents($file), $file);
				};
				break;
			/**
			 * Combine css and js files for Web Component into resulting files in order to optimize loading process
			 */
			case 'html':
				/**
				 * For CSP-compatible HTML files we need to know destination to put there additional JS/CSS files
				 */
				$destination = Config::instance()->core['vulcanization'] ? false : PUBLIC_CACHE;
				$callback    = function ($content, $file) use ($filename_prefix, $destination) {
					$base_filename = "$filename_prefix-".basename($file).'+'.substr(md5($file), 0, 5);
					return $content.Includes_processing::html(file_get_contents($file), $file, $base_filename, $destination);
				};
				break;
			case 'js':
				$callback = function ($content, $file) {
					return $content.Includes_processing::js(file_get_contents($file));
				};
				if ($filename_prefix == 'System') {
					$content = 'window.cs={Language:'._json_encode(Language::instance()).'};';
					$content .= 'window.requirejs={paths:'._json_encode($this->get_requirejs_paths()).'};';
				}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return array_reduce($files, $callback, $content);
	}
}
