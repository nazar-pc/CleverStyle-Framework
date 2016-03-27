<?php
/**
 * @package   Composer assets
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\plugins\Composer_assets;
use
	cs\Config,
	Exception,
	cs\Page\Includes_processing,
	Less_Parser,
	Leafo\ScssPhp\Compiler as Scss_compiler;

class Assets_processing {
	/**
	 * @param string     $package_name
	 * @param string     $package_dir
	 * @param string     $target_dir
	 * @param string[][] $includes_map
	 */
	static function run ($package_name, $package_dir, $target_dir, &$includes_map) {
		self::save_content(
			self::get_content(
				self::get_files($package_name),
				$package_name,
				$package_dir,
				$target_dir
			),
			$package_name,
			$target_dir,
			$includes_map
		);
	}
	/**
	 * @param string $package_name
	 *
	 * @return string[]
	 */
	protected static function get_files ($package_name) {
		$Config = Config::instance();
		$files  = [];
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			if (file_exists(MODULES."/$module_name/meta.json")) {
				$meta    = file_get_json(MODULES."/$module_name/meta.json");
				$files[] = self::extract_files($meta, $package_name);
			}
		}
		foreach ($Config->components['plugins'] as $plugin_name) {
			if (file_exists(PLUGINS."/$plugin_name/meta.json")) {
				$meta    = file_get_json(PLUGINS."/$plugin_name/meta.json");
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
	 * @param string[] $files
	 * @param string   $package_name
	 * @param string   $package_dir
	 * @param string   $target_dir
	 *
	 * @return string[][]
	 */
	protected static function get_content ($files, $package_name, $package_dir, $target_dir) {
		$content = [];
		if ($files) {
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
		}
		foreach ($files as $file) {
			$file = "$package_dir/$file";
			switch (file_extension($file)) {
				case 'js':
					$content['js'][] = file_get_contents($file);
					break;
				case 'css':
					$content['css'][] = Includes_processing::css(
						file_get_contents($file),
						$file
					);
					break;
				case 'html':
					$content['html'][] = Includes_processing::html(
						file_get_contents($file),
						$file,
						$package_name,
						$target_dir
					);
					break;
				case 'less':
					try {
						$content['css'][] = Includes_processing::css(
							(new Less_Parser)->parseFile($file)->getCss(),
							$file
						);
					} catch (Exception $e) {
					}
					break;
				case 'scss':
					$content['css'][] = Includes_processing::css(
						(new Scss_compiler)->compile(file_get_contents($file)),
						$file
					);
					break;
			}
		}
		return $content;
	}
	/**
	 * @param string[][] $content
	 * @param string     $package_name
	 * @param string     $target_dir
	 * @param string[][] $includes_map
	 */
	protected static function save_content ($content, $package_name, $target_dir, &$includes_map) {
		foreach ($content as $extension => $c) {
			$target_file = "$target_dir/index.$extension";
			file_put_contents($target_file, implode('', $c), FILE_APPEND);
			$includes_map[$package_name][$extension][] = $target_file;
		}
	}
}
