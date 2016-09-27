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
	cs\Page\Includes_processing;

class Cache {
	/**
	 * @param \cs\Config   $Config
	 * @param \cs\Language $L
	 * @param string       $pcache_basename_path
	 * @param string       $theme
	 */
	public static function rebuild ($Config, $L, $pcache_basename_path, $theme) {
		if (!file_exists("$pcache_basename_path.json")) {
			static::rebuild_normal($Config, $pcache_basename_path, $theme);
			Event::instance()->fire('System/Page/rebuild_cache');
			static::rebuild_optimized($pcache_basename_path);
			static::rebuild_webcomponents_polyfill();
		}
		/**
		 * We take hash of languages in order to take into account when list of active languages has changed (and generate cache for all acive languages)
		 */
		$languages_hash = static::get_hash_of(implode('', $Config->core['active_languages']));
		if (!file_exists(PUBLIC_CACHE."/languages-$languages_hash.json")) {
			static::rebuild_languages($Config, $L, $languages_hash);
		}
	}
	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected static function get_hash_of ($content) {
		return substr(md5($content), 0, 5);
	}
	/**
	 * @param \cs\Config $Config
	 * @param string     $pcache_basename_path
	 */
	protected static function rebuild_normal ($Config, $pcache_basename_path, $theme) {
		list($dependencies, $includes_map) = Collecting::get_includes_dependencies_and_map($Config, $theme);
		$compressed_includes_map    = [];
		$not_embedded_resources_map = [];
		/** @noinspection ForeachSourceInspection */
		foreach ($includes_map as $filename_prefix => $local_includes) {
			$compressed_includes_map[$filename_prefix] = static::cache_compressed_includes_files(
				"$pcache_basename_path:".str_replace('/', '+', $filename_prefix),
				$local_includes,
				$Config->core['vulcanization'],
				$not_embedded_resources_map
			);
		}
		unset($includes_map, $filename_prefix, $local_includes);
		file_put_json("$pcache_basename_path.json", [$dependencies, $compressed_includes_map, array_filter($not_embedded_resources_map)]);
	}
	/**
	 * @param string $pcache_basename_path
	 */
	protected static function rebuild_optimized ($pcache_basename_path) {
		list(, $compressed_includes_map, $preload_source) = file_get_json("$pcache_basename_path.json");
		$preload = [array_values($compressed_includes_map['System'])];
		/** @noinspection ForeachSourceInspection */
		foreach ($compressed_includes_map['System'] as $path) {
			if (isset($preload_source[$path])) {
				$preload[] = $preload_source[$path];
			}
		}
		unset($compressed_includes_map['System']);
		$optimized_includes = array_flip(array_merge(...array_values(array_map('array_values', $compressed_includes_map))));
		$preload            = array_merge(...$preload);
		file_put_json("$pcache_basename_path.optimized.json", [$optimized_includes, $preload]);
	}
	protected static function rebuild_webcomponents_polyfill () {
		$webcomponents_js = file_get_contents(DIR.'/includes/js/WebComponents-polyfill/webcomponents-custom.min.js');
		file_put_contents(PUBLIC_CACHE.'/webcomponents.js', $webcomponents_js, LOCK_EX | FILE_BINARY);
		file_put_contents(PUBLIC_CACHE.'/webcomponents.js.hash', static::get_hash_of($webcomponents_js), LOCK_EX | FILE_BINARY);
	}
	/**
	 * @param \cs\Config   $Config
	 * @param \cs\Language $L
	 * @param string       $languages_hash
	 */
	protected static function rebuild_languages ($Config, $L, $languages_hash) {
		$current_language = $L->clanguage;
		$languages_map    = [];
		/** @noinspection ForeachSourceInspection */
		foreach ($Config->core['active_languages'] as $language) {
			$L->change($language);
			/** @noinspection DisconnectedForeachInstructionInspection */
			$translations             = _json_encode($L);
			$language_hash            = static::get_hash_of($translations);
			$languages_map[$language] = $language_hash;
			file_put_contents(PUBLIC_CACHE."/languages-$language-$language_hash.js", "define($translations);");
		}
		$L->change($current_language);
		file_put_json(PUBLIC_CACHE."/languages-$languages_hash.json", $languages_map);
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
	protected static function cache_compressed_includes_files ($target_file_path, $includes, $vulcanization, &$not_embedded_resources_map) {
		$local_includes = [];
		foreach ($includes as $extension => $files) {
			$not_embedded_resources = [];
			$content                = static::cache_compressed_includes_files_single(
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
			file_put_contents($file_path, $content, LOCK_EX | FILE_BINARY);
			$relative_path                              = '/storage/pcache/'.basename($file_path).'?'.static::get_hash_of($content);
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
	protected static function cache_compressed_includes_files_single ($extension, $target_file_path, $files, $vulcanization, &$not_embedded_resources) {
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
					$content = 'window.cs={};window.requirejs={paths:'._json_encode(RequireJS::get_paths()).'};';
				}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		$content .= array_reduce($files, $callback);
		if ($extension == 'html') {
			$file_path = "$target_file_path-$extension";
			$content   = Includes_processing::html($content, $file_path, $file_path, $vulcanization);
		}
		return $content;
	}
}
