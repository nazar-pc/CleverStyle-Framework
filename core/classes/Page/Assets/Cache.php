<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page\Assets;
use
	cs\Event,
	cs\Page\Assets_processing;

class Cache {
	/**
	 * @param \cs\Config   $Config
	 * @param \cs\Language $L
	 * @param string       $theme
	 */
	public static function rebuild ($Config, $L, $theme) {
		$public_cache_basename_path = PUBLIC_CACHE.'/'.$theme;
		if (!file_exists("$public_cache_basename_path.json")) {
			static::rebuild_normal($Config, $public_cache_basename_path, $theme);
			Event::instance()->fire('System/Page/rebuild_cache');
			static::rebuild_optimized($public_cache_basename_path);
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
	 * @param string     $public_cache_basename_path
	 * @param string     $theme
	 */
	protected static function rebuild_normal ($Config, $public_cache_basename_path, $theme) {
		list($dependencies, $assets_map) = Collecting::get_assets_dependencies_and_map($Config, $theme);
		$compressed_assets_map      = [];
		$not_embedded_resources_map = [];
		/** @noinspection ForeachSourceInspection */
		foreach ($assets_map as $filename_prefix => $local_assets) {
			$compressed_assets_map[$filename_prefix] = static::cache_compressed_assets_files(
				"$public_cache_basename_path:".str_replace('/', '+', $filename_prefix),
				$local_assets,
				$Config->core['vulcanization'],
				$not_embedded_resources_map
			);
		}
		unset($assets_map, $filename_prefix, $local_assets);
		file_put_json("$public_cache_basename_path.json", [$dependencies, $compressed_assets_map, array_filter($not_embedded_resources_map)]);
	}
	/**
	 * @param string $public_cache_basename_path
	 */
	protected static function rebuild_optimized ($public_cache_basename_path) {
		list(, $compressed_assets_map, $preload_source) = file_get_json("$public_cache_basename_path.json");
		$preload = [array_values($compressed_assets_map['System'])];
		/** @noinspection ForeachSourceInspection */
		foreach ($compressed_assets_map['System'] as $path) {
			if (isset($preload_source[$path])) {
				$preload[] = $preload_source[$path];
			}
		}
		unset($compressed_assets_map['System']);
		$optimized_assets = array_merge(...array_values(array_map('array_values', $compressed_assets_map)));
		$preload          = array_merge(...$preload);
		file_put_json("$public_cache_basename_path.optimized.json", [$optimized_assets, $preload]);
	}
	protected static function rebuild_webcomponents_polyfill () {
		$webcomponents_js = file_get_contents(DIR.'/assets/js/WebComponents-polyfill/webcomponents-custom.min.js');
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
	 * @param string[][] $assets                     Array of paths to files, may have keys: `css` and/or `js` and/or `html`
	 * @param bool       $vulcanization              Whether to put combined files separately or to make included assets built-in (vulcanization)
	 * @param string[][] $not_embedded_resources_map Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because
	 *                                               of CSP
	 *
	 * @return array
	 */
	protected static function cache_compressed_assets_files ($target_file_path, $assets, $vulcanization, &$not_embedded_resources_map) {
		$local_assets = [];
		foreach ($assets as $extension => $files) {
			$not_embedded_resources = [];
			$content                = static::cache_compressed_assets_files_single(
				$extension,
				$target_file_path,
				$files,
				$vulcanization,
				$not_embedded_resources
			);
			foreach ($not_embedded_resources as &$resource) {
				if (strpos($resource, '/') !== 0) {
					$resource = "/storage/public_cache/$resource";
				}
			}
			unset($resource);
			$file_path = "$target_file_path.$extension";
			file_put_contents($file_path, $content, LOCK_EX | FILE_BINARY);
			$relative_path                              = '/storage/public_cache/'.basename($file_path).'?'.static::get_hash_of($content);
			$local_assets[$extension]                   = $relative_path;
			$not_embedded_resources_map[$relative_path] = $not_embedded_resources;
		}
		return $local_assets;
	}
	/**
	 * @param string   $extension
	 * @param string   $target_file_path
	 * @param string[] $files
	 * @param bool     $vulcanization          Whether to put combined files separately or to make included assets built-in (vulcanization)
	 * @param string[] $not_embedded_resources Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because of CSP
	 *
	 * @return string
	 */
	protected static function cache_compressed_assets_files_single ($extension, $target_file_path, $files, $vulcanization, &$not_embedded_resources) {
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
					return $content.Assets_processing::css(file_get_contents($file), $file, $not_embedded_resources);
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
					return
						$content.
						Assets_processing::html(
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
					return $content.Assets_processing::js(file_get_contents($file));
				};
				if (substr($target_file_path, -7) == ':System') {
					$content = 'window.cs={};window.requirejs='._json_encode(RequireJS::get_config()).';';
				}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		$content .= array_reduce($files, $callback);
		if ($extension == 'html') {
			$file_path = "$target_file_path-$extension";
			$content   = Assets_processing::html($content, $file_path, $file_path, $vulcanization);
		}
		return $content;
	}
}
