<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page\Includes;
use
	cs\Event,
	cs\Language,
	cs\Page\Includes_processing;

trait Cache {
	/**
	 * @param \cs\Config $Config
	 */
	protected function rebuild_cache ($Config) {
		if (!file_exists("$this->pcache_basename_path.json")) {
			list($dependencies, $includes_map) = $this->get_includes_dependencies_and_map($Config);
			$compressed_includes_map    = [];
			$not_embedded_resources_map = [];
			foreach ($includes_map as $filename_prefix => $local_includes) {
				$compressed_includes_map[$filename_prefix] = $this->cache_compressed_includes_files(
					"$this->pcache_basename_path:".str_replace('/', '+', $filename_prefix),
					$local_includes,
					$Config->core['vulcanization'],
					$not_embedded_resources_map
				);
			}
			unset($includes_map, $filename_prefix, $local_includes);
			file_put_json("$this->pcache_basename_path.json", [$dependencies, $compressed_includes_map, array_filter($not_embedded_resources_map)]);
			Event::instance()->fire('System/Page/rebuild_cache');
			$this->rebuild_cache_optimized();
		}
	}
	protected function rebuild_cache_optimized () {
		list(, $compressed_includes_map, $preload_source) = file_get_json("$this->pcache_basename_path.json");
		$preload = [array_values($compressed_includes_map['System'])];
		foreach ($compressed_includes_map['System'] as $path) {
			if (isset($preload_source[$path])) {
				$preload[] = $preload_source[$path];
			}
		}
		unset($compressed_includes_map['System']);
		$optimized_includes = array_flip(array_merge(...array_values(array_map('array_values', $compressed_includes_map))));
		$preload            = array_merge(...$preload);
		file_put_json("$this->pcache_basename_path.optimized.json", [$optimized_includes, $preload]);
	}
	/**
	 * Creates cached version of given HTML, JS and CSS files.
	 * Resulting files names consist of `$filename_prefix` and extension.
	 *
	 * @param string     $target_file_path
	 * @param string[][] $includes                   Array of paths to files, may have keys: `css` and/or `js` and/or `html`
	 * @param bool       $vulcanization              Whether to put combined files separately or to make includes built-in (vulcanization)
	 * @param string[][] $not_embedded_resources_map Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because
	 *                                               of CSP
	 *
	 * @return array
	 */
	protected function cache_compressed_includes_files ($target_file_path, $includes, $vulcanization, &$not_embedded_resources_map) {
		$local_includes = [];
		foreach ($includes as $extension => $files) {
			$not_embedded_resources = [];
			$content                = $this->cache_compressed_includes_files_single(
				$extension,
				$target_file_path,
				$files,
				$vulcanization,
				$not_embedded_resources
			);
			foreach ($not_embedded_resources as &$resource) {
				if (strpos($resource, '/') !== 0) {
					$resource = "/storage/pcache/$resource";
				}
			}
			unset($resource);
			$file_path = "$target_file_path.$extension";
			file_put_contents($file_path, gzencode($content, 9), LOCK_EX | FILE_BINARY);
			$relative_path                              = '/storage/pcache/'.basename($file_path).'?'.substr(md5($content), 0, 5);
			$local_includes[$extension]                 = $relative_path;
			$not_embedded_resources_map[$relative_path] = $not_embedded_resources;
		}
		return $local_includes;
	}
	/**
	 * @param string   $extension
	 * @param string   $target_file_path
	 * @param string[] $files
	 * @param bool     $vulcanization          Whether to put combined files separately or to make includes built-in (vulcanization)
	 * @param string[] $not_embedded_resources Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because of CSP
	 *
	 * @return string
	 */
	protected function cache_compressed_includes_files_single ($extension, $target_file_path, $files, $vulcanization, &$not_embedded_resources) {
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
				$callback = function ($content, $file) use (&$not_embedded_resources) {
					return $content.Includes_processing::css(file_get_contents($file), $file, $not_embedded_resources);
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
				$callback = function ($content, $file) use (&$not_embedded_resources) {
					return $content.Includes_processing::html(
						file_get_contents($file),
						$file,
						'',
						true,
						$not_embedded_resources
					);
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
		$content = array_reduce($files, $callback, $content);
		if ($extension == 'html') {
			$file_path = "$target_file_path-$extension";
			$content   = Includes_processing::html($content, $file_path, $file_path, $vulcanization);
		}
		return $content;
	}
}
