<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page\Includes;
use
	cs\Language,
	cs\Page\Includes_processing;

trait Cache {
	/**
	 * Creates cached version of given HTML, JS and CSS files.
	 * Resulting files names consist of `$filename_prefix` and extension.
	 *
	 * @param string     $target_file_path
	 * @param string[][] $includes      Array of paths to files, may have keys: `css` and/or `js` and/or `html`
	 * @param bool       $vulcanization Whether to put combined files separately or to make includes built-in (vulcanization)
	 *
	 * @return array
	 */
	protected function cache_compressed_includes_files ($target_file_path, $includes, $vulcanization) {
		$local_includes = [];
		foreach ($includes as $extension => $files) {
			$content   = $this->cache_compressed_includes_files_single($extension, $target_file_path, $files, $vulcanization);
			$file_path = "$target_file_path.$extension";
			file_put_contents($file_path, gzencode($content, 9), LOCK_EX | FILE_BINARY);
			$local_includes[$extension] = 'storage/pcache/'.basename($file_path).'?'.substr(md5($content), 0, 5);
		}
		return $local_includes;
	}
	/**
	 * @param string   $extension
	 * @param string   $target_file_path
	 * @param string[] $files
	 * @param bool     $vulcanization Whether to put combined files separately or to make includes built-in (vulcanization)
	 *
	 * @return mixed
	 */
	protected function cache_compressed_includes_files_single ($extension, $target_file_path, $files, $vulcanization) {
		$content = '';
		switch ($extension) {
			/**
			 * Insert external elements into resulting css file.
			 * It is needed, because those files will not be copied into new destination of resulting css file.
			 */
			case 'css':
				/**
				 * @param string $content
				 * @param string $file
				 *
				 * @return string
				 */
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
				 *
				 * @param string $content
				 * @param string $file
				 *
				 * @return string
				 */
				$callback = function ($content, $file) use ($target_file_path, $vulcanization) {
					$base_target_file_path = "$target_file_path-".basename($file).'+'.substr(md5($file), 0, 5);
					return $content.Includes_processing::html(file_get_contents($file), $file, $base_target_file_path, $vulcanization);
				};
				break;
			case 'js':
				/**
				 * @param string $content
				 * @param string $file
				 *
				 * @return string
				 */
				$callback = function ($content, $file) {
					return $content.Includes_processing::js(file_get_contents($file));
				};
				if (substr($target_file_path, -7) == ':System') {
					$content = 'window.cs={Language:'._json_encode(Language::instance()).'};';
					$content .= 'window.requirejs={paths:'._json_encode($this->get_requirejs_paths()).'};';
				}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return array_reduce($files, $callback, $content);
	}
}
