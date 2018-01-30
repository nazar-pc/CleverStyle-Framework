<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config;

trait languages {
	protected static $languages_options_keys = [
		'language',
		'active_languages',
		'multilingual'
	];
	/**
	 * Get languages settings
	 *
	 * @return array
	 */
	public static function admin_languages_get_settings () {
		$Config = Config::instance();
		return $Config->core(static::$languages_options_keys) + [
			'languages' => static::get_languages_array(),
			'applied'   => $Config->cancel_available()
		];
	}
	/**
	 * @return string[]
	 */
	protected static function get_languages_array () {
		$languages = array_unique(
			array_merge(
				_mb_substr(get_files_list(LANGUAGES, '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
				_mb_substr(get_files_list(LANGUAGES, '/^.*?\.json$/i', 'f'), 0, -5) ?: []
			)
		);
		asort($languages);
		return $languages;
	}
	/**
	 * Apply language settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_languages_apply_settings ($Request) {
		static::admin_core_options_apply($Request, static::$languages_options_keys);
	}
	/**
	 * Save language settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_languages_save_settings ($Request) {
		static::admin_core_options_save($Request, static::$languages_options_keys);
	}
	/**
	 * Cancel language settings
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_languages_cancel_settings () {
		static::admin_core_options_cancel();
	}
}
