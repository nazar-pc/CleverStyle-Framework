<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs\Page\Assets;
use
	cs\Event,
	cs\Page\Assets_processing;

class Cache {
	/**
	 * @var string
	 */
	protected $public_cache;
	/**
	 * @param string $public_cache
	 */
	public function __construct ($public_cache = PUBLIC_CACHE) {
		$this->public_cache = $public_cache;
	}
	/**
	 * @param \cs\Config   $Config
	 * @param \cs\Language $L
	 * @param string       $theme
	 */
	public function rebuild ($Config, $L, $theme) {
		if (!file_exists("$this->public_cache/$theme.json")) {
			$this->rebuild_normal($Config, $theme);
			Event::instance()->fire('System/Page/rebuild_cache');
			$this->rebuild_optimized($theme);
			$this->rebuild_webcomponents_polyfill();
		}
		/**
		 * We take hash of languages in order to take into account when list of active languages has changed (and generate cache for all acive languages)
		 */
		$languages_hash = md5(implode('', $Config->core['active_languages']));
		if (!file_exists("$this->public_cache/languages-$languages_hash.json")) {
			$this->rebuild_languages($Config, $L, $languages_hash);
		}
	}
	/**
	 * @param \cs\Config $Config
	 * @param string     $theme
	 */
	protected function rebuild_normal ($Config, $theme) {
		list($dependencies, $assets_map) = Collecting::get_assets_dependencies_and_map($Config, $theme);
		$compressed_assets_map      = [];
		$not_embedded_resources_map = [];
		/** @noinspection ForeachSourceInspection */
		foreach ($assets_map as $filename_prefix => $local_assets) {
			$compressed_assets_map[$filename_prefix] = $this->cache_compressed_assets_files(
				$filename_prefix,
				$local_assets,
				$Config->core['vulcanization'],
				$not_embedded_resources_map
			);
		}
		unset($assets_map, $filename_prefix, $local_assets);
		file_put_json("$this->public_cache/$theme.json", [$dependencies, $compressed_assets_map, array_filter($not_embedded_resources_map)]);
	}
	/**
	 * @param string $theme
	 */
	protected function rebuild_optimized ($theme) {
		list(, $compressed_assets_map, $preload_source) = file_get_json("$this->public_cache/$theme.json");
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
		file_put_json("$this->public_cache/$theme.optimized.json", [$optimized_assets, $preload]);
	}
	protected function rebuild_webcomponents_polyfill () {
		$webcomponents_js = file_get_contents(DIR.'/assets/js/WebComponents-polyfill/webcomponents-hi-sd-ce.min.js');
		$hash             = md5($webcomponents_js);
		file_put_contents("$this->public_cache/$hash.js", $webcomponents_js, LOCK_EX | FILE_BINARY);
		file_put_contents("$this->public_cache/webcomponents.js.hash", $hash, LOCK_EX | FILE_BINARY);
	}
	/**
	 * @param \cs\Config   $Config
	 * @param \cs\Language $L
	 * @param string       $languages_hash
	 */
	protected function rebuild_languages ($Config, $L, $languages_hash) {
		$current_language = $L->clanguage;
		$languages_map    = [];
		/** @noinspection ForeachSourceInspection */
		foreach ($Config->core['active_languages'] as $language) {
			$L->change($language);
			$translations             = _json_encode($L);
			$language_hash            = md5($translations);
			$languages_map[$language] = $language_hash;
			file_put_contents("$this->public_cache/$language_hash.js", "define($translations);");
		}
		$L->change($current_language);
		file_put_json("$this->public_cache/languages-$languages_hash.json", $languages_map);
	}
	/**
	 * Creates cached version of given HTML, JS and CSS files.
	 * Resulting files names consist of `$filename_prefix` and extension.
	 *
	 * @param string     $filename_prefix
	 * @param string[][] $assets                     Array of paths to files, may have keys: `css` and/or `js` and/or `html`
	 * @param bool       $vulcanization              Whether to put combined files separately or to make included assets built-in (vulcanization)
	 * @param string[][] $not_embedded_resources_map Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because
	 *                                               of CSP
	 *
	 * @return array
	 */
	protected function cache_compressed_assets_files ($filename_prefix, $assets, $vulcanization, &$not_embedded_resources_map) {
		$local_assets = [];
		foreach ($assets as $extension => $files) {
			$not_embedded_resources = [];
			$content                = $this->cache_compressed_assets_files_single(
				$extension,
				$filename_prefix,
				$files,
				$vulcanization,
				$not_embedded_resources
			);
			foreach ($not_embedded_resources as &$resource) {
				if (strpos($resource, '/') !== 0) {
					$resource = "/storage/public_cache/$resource";
				}
			}
			$file_path = $this->public_cache.'/'.md5($content).'.'.$extension;
			file_put_contents($file_path, $content, LOCK_EX | FILE_BINARY);
			$relative_path                              = '/storage/public_cache/'.basename($file_path);
			$local_assets[$extension]                   = $relative_path;
			$not_embedded_resources_map[$relative_path] = $not_embedded_resources;
		}
		return $local_assets;
	}
	/**
	 * @param string   $extension
	 * @param string   $filename_prefix
	 * @param string[] $files
	 * @param bool     $vulcanization          Whether to put combined files separately or to make included assets built-in (vulcanization)
	 * @param string[] $not_embedded_resources Resources like images/fonts might not be embedded into resulting CSS because of big size or CSS/JS because of CSP
	 *
	 * @return string
	 */
	protected function cache_compressed_assets_files_single ($extension, $filename_prefix, $files, $vulcanization, &$not_embedded_resources) {
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
					return $content.Assets_processing::css(file_get_contents($file), $file, $this->public_cache, $not_embedded_resources);
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
							$this->public_cache,
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
				if ($filename_prefix == 'System') {
					$content = 'window.cs={};window.requirejs='._json_encode(RequireJS::get_config()).';';
				}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		$content .= array_reduce($files, $callback);
		if ($extension == 'html') {
			$content = Assets_processing::html($content, '', $this->public_cache, $vulcanization);
		}
		return $content;
	}
}
