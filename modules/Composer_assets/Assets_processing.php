<?php
/**
 * @package   Composer assets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer_assets;
use
	cs\Config,
	Exception,
	cs\Page\Includes_processing,
	Less_Parser,
	Leafo\ScssPhp\Compiler as Scss_compiler;

class Assets_processing {
	/**
	 * @param string $package_name
	 * @param string $package_dir
	 * @param string $composer_assets_dir
	 *
	 * @return string[][]
	 */
	static function run ($package_name, $package_dir, $composer_assets_dir) {
		$package_name = explode('/', $package_name, 2)[1];
		$Config       = Config::instance();
		$files        = self::get_files($Config, $package_name);
		if (!$files) {
			return [];
		}
		$target_dir = "$composer_assets_dir/$package_name";
		@mkdir($target_dir, 0770, true);
		file_put_contents(
			"$target_dir/.htaccess",
			/** @lang ApacheConfig */
			<<<HTACCESS
<FilesMatch "\.css$">
	Header set Content-Type text/css
</FilesMatch>
HTACCESS
		);
		return self::save_content(
			self::get_content($Config, $files, $package_name, $package_dir, $target_dir),
			$target_dir
		);
	}
	/**
	 * @param Config $Config
	 * @param string $package_name
	 *
	 * @return string[]
	 */
	protected static function get_files ($Config, $package_name) {
		$files = [];
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			if (file_exists(MODULES."/$module_name/meta.json")) {
				$meta    = file_get_json(MODULES."/$module_name/meta.json");
				$files[] = self::extract_files($meta, $package_name);
			}
		}
		return array_unique(array_merge(...$files));
	}
	/**
	 * @param array  $meta
	 * @param string $package_name
	 *
	 * @return string[]
	 */
	protected static function extract_files ($meta, $package_name) {
		$meta += ['require_bower' => [], 'require_npm' => []];
		$packages = $meta['require_bower'] + $meta['require_npm'];
		return isset($packages[$package_name]['files']) ? $packages[$package_name]['files'] : [];
	}
	/**
	 * @param Config   $Config
	 * @param string[] $files
	 * @param string   $package_name
	 * @param string   $package_dir
	 * @param string   $target_dir
	 *
	 * @return string[][]
	 */
	protected static function get_content ($Config, $files, $package_name, $package_dir, $target_dir) {
		$content = [];
		foreach ($files as $file) {
			$file = "$package_dir/$file";
			switch (file_extension($file)) {
				case 'js':
					$content['js'][] = file_get_contents($file);
					break;
				case 'css':
					$content['css'][] = Includes_processing::css(file_get_contents($file), $file);
					break;
				case 'html':
					$content['html'][] = Includes_processing::html(
						file_get_contents($file),
						$file,
						"$target_dir/$package_name",
						$Config->core['vulcanization']
					);
					break;
				case 'less':
					$content['css'][] = Includes_processing::css(self::compile_less($file), $file);
					break;
				case 'scss':
					$content['css'][] = Includes_processing::css(self::compile_scss($file), $file);
					break;
			}
		}
		return $content;
	}
	/**
	 * @param string $less_file
	 *
	 * @return string CSS
	 */
	protected static function compile_less ($less_file) {
		try {
			return (new Less_Parser)->parseFile($less_file)->getCss();
		} catch (Exception $e) {
			return '';
		}
	}
	/**
	 * @param string $scss_file
	 *
	 * @return string CSS
	 */
	protected static function compile_scss ($scss_file) {
		return (new Scss_compiler)->compile(file_get_contents($scss_file));
	}
	/**
	 * @param string[][] $content
	 * @param string     $target_dir
	 *
	 * @return string[][]
	 */
	protected static function save_content ($content, $target_dir) {
		$result = [];
		foreach ($content as $extension => $c) {
			$target_file = "$target_dir/index.$extension";
			file_put_contents($target_file, implode('', $c), FILE_APPEND);
			$result[$extension][] = $target_file;
		}
		return $result;
	}
}
