<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language,
	cs\Session,
	cs\modules\System\Packages_manipulation;

trait themes {
	/**
	 * @param \cs\Request $Request
	 *
	 * @return mixed
	 *
	 * @throws ExitException
	 */
	static function admin_themes_get ($Request) {
		if ($Request->route_path(3) == 'update_dependencies') {
			/**
			 * Get dependencies for theme during update
			 */
			return static::get_update_dependencies_for_theme($Request->route_path[2]);
		} elseif ($Request->route_path(2) == 'current') {
			/**
			 * Get current theme
			 */
			return static::get_current_theme();
		} else {
			/**
			 * Get array of themes in extended form
			 */
			return static::get_themes_list();
		}
	}
	/**
	 * @param string $theme
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	protected static function get_update_dependencies_for_theme ($theme) {
		$themes = get_files_list(THEMES, false, 'd');
		if (!in_array($theme, $themes, true)) {
			throw new ExitException(404);
		}
		$tmp_location = TEMP.'/System/admin/'.Session::instance()->get_id().'.phar';
		$tmp_dir      = "phar://$tmp_location";
		if (
			!file_exists(THEMES."/$theme/meta.json") ||
			!file_exists("$tmp_dir/meta.json")
		) {
			throw new ExitException(400);
		}
		$existing_meta = file_get_json(THEMES."/$theme/meta.json");
		$new_meta      = file_get_json("$tmp_dir/meta.json");
		if (
			$existing_meta['package'] !== $new_meta['package'] ||
			$existing_meta['category'] !== $new_meta['category']
		) {
			throw new ExitException(Language::instance()->this_is_not_theme_installer_file, 400);
		}
		$dependencies = [];
		if (version_compare($new_meta['version'], $existing_meta['version'], '<')) {
			$dependencies['update_older'] = [
				'from' => $existing_meta['version'],
				'to'   => $new_meta['version']
			];
		}
		return $dependencies;
	}
	/**
	 * @return string
	 */
	protected static function get_current_theme () {
		return Config::instance()->core['theme'];
	}
	/**
	 * @return array
	 */
	protected static function get_themes_list () {
		$themes = get_files_list(THEMES, false, 'd');
		asort($themes);
		$themes_list = [];
		foreach ($themes as $theme_name) {
			$theme = [
				'name' => $theme_name
			];
			/**
			 * Check if readme available
			 */
			static::check_theme_feature_availability($theme, 'readme');
			/**
			 * Check if license available
			 */
			static::check_theme_feature_availability($theme, 'license');
			if (file_exists(THEMES."/$theme_name/meta.json")) {
				$theme['meta'] = file_get_json(THEMES."/$theme_name/meta.json");
			}
			$themes_list[] = $theme;
		}
		return $themes_list;
	}
	/**
	 * @param array  $theme
	 * @param string $feature
	 */
	protected static function check_theme_feature_availability (&$theme, $feature) {
		/**
		 * Check if feature available
		 */
		$file = file_exists_with_extension(THEMES."/$theme[name]/$feature", ['txt', 'html']);
		if ($file) {
			$theme[$feature] = [
				'type'    => substr($file, -3) == 'txt' ? 'txt' : 'html',
				'content' => file_get_contents($file)
			];
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_themes_put ($Request) {
		if ($Request->route_path(2) == 'current') {
			$theme = $Request->data('theme');
			if (!$theme) {
				throw new ExitException(400);
			}
			/**
			 * Set current theme
			 */
			static::set_current_theme($theme);
		} else {
			throw new ExitException(400);
		}
	}
	/**
	 * Provides next events:
	 *  admin/System/themes/current/before
	 *  ['name' => theme_name]
	 *
	 *  admin/System/themes/current/after
	 *  ['name' => theme_name]
	 *
	 * @param string $theme
	 *
	 * @throws ExitException
	 */
	protected static function set_current_theme ($theme) {
		$Config = Config::instance();
		$themes = get_files_list(THEMES, false, 'd');
		if (!in_array($theme, $themes, true)) {
			throw new ExitException(404);
		}
		if ($theme == $Config->core['theme']) {
			throw new ExitException(400);
		}
		if (!Event::instance()->fire(
			'admin/System/themes/current/before',
			[
				'name' => $theme
			]
		)
		) {
			throw new ExitException(500);
		}
		$Config->core['theme'] = $theme;
		if (!$Config->save()) {
			throw new ExitException(500);
		}
		Event::instance()->fire(
			'admin/System/themes/current/after',
			[
				'name' => $theme
			]
		);
	}
	/**
	 * Extract uploaded theme
	 *
	 * @throws ExitException
	 */
	static function admin_themes_extract () {
		$L            = Language::instance();
		$tmp_location = TEMP.'/System/admin/'.Session::instance()->get_id().'.phar';
		$tmp_dir      = "phar://$tmp_location";
		if (
			!file_exists($tmp_location) ||
			!file_exists("$tmp_dir/meta.json")
		) {
			throw new ExitException(400);
		}
		$new_meta = file_get_json("$tmp_dir/meta.json");
		if ($new_meta['category'] !== 'themes') {
			throw new ExitException($L->this_is_not_theme_installer_file, 400);
		}
		if (!Packages_manipulation::install_extract(THEMES."/$new_meta[package]", $tmp_location)) {
			throw new ExitException($L->theme_files_unpacking_error, 500);
		}
	}
	/**
	 * Update theme
	 *
	 * Provides next events:
	 *  admin/System/themes/update/before
	 *  ['name' => theme_name]
	 *
	 *  admin/System/themes/update/after
	 *  ['name' => theme_name]
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_themes_update ($Request) {
		$L      = Language::instance();
		$theme  = $Request->route_path(2);
		$themes = get_files_list(THEMES, false, 'd');
		if (!in_array($theme, $themes, true)) {
			throw new ExitException(404);
		}
		$tmp_location = TEMP.'/System/admin/'.Session::instance()->get_id().'.phar';
		$tmp_dir      = "phar://$tmp_location";
		$theme_dir    = THEMES."/$theme";
		if (
			!file_exists($tmp_location) ||
			!file_exists("$theme_dir/meta.json") ||
			!file_exists("$tmp_dir/meta.json")
		) {
			throw new ExitException(400);
		}
		$new_meta = file_get_json("$tmp_dir/meta.json");
		if (
			$new_meta['package'] !== $theme ||
			$new_meta['category'] !== 'themes'
		) {
			throw new ExitException($L->this_is_not_theme_installer_file, 400);
		}
		if (!Event::instance()->fire(
			'admin/System/themes/update/before',
			[
				'name' => $theme
			]
		)
		) {
			throw new ExitException(500);
		}
		if (!is_writable($theme_dir)) {
			throw new ExitException($L->cant_unpack_theme_no_write_permissions, 500);
		}
		if (!Packages_manipulation::update_extract(THEMES."/$theme", $tmp_location)) {
			throw new ExitException($L->theme_files_unpacking_error, 500);
		}
		clean_pcache();
		Event::instance()->fire(
			'admin/System/themes/update/after',
			[
				'name' => $theme
			]
		);
	}
	/**
	 * Delete theme completely
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_themes_delete ($Request) {
		$Config = Config::instance();
		$theme  = $Request->route_path(2);
		$themes = get_files_list(THEMES, false, 'd');
		if (
			$theme == Config::SYSTEM_THEME ||
			$Config->core['theme'] == $theme ||
			!in_array($theme, $themes, true)
		) {
			throw new ExitException(400);
		}
		if (!rmdir_recursive(THEMES."/$theme")) {
			throw new ExitException(500);
		}
	}
}
