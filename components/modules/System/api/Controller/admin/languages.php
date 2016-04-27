<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config,
	cs\ExitException;

trait languages {
	/**
	 * Get languages settings
	 */
	static function admin_languages_get_settings () {
		$Config = Config::instance();
		return [
			'language'         => $Config->core['language'],
			'active_languages' => $Config->core['active_languages'],
			'languages'        => static::get_languages_array(),
			'multilingual'     => $Config->core['multilingual'],
			'applied'          => $Config->cancel_available()
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
	 * @throws ExitException
	 */
	static function admin_languages_apply_settings ($Request) {
		static::admin_languages_settings_common($Request);
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected static function admin_languages_settings_common ($Request) {
		$data = $Request->data('language', 'active_languages', 'multilingual');
		if (
			!$data ||
			!is_array($data['active_languages']) ||
			!in_array($data['language'], $data['active_languages'], true) ||
			array_diff($data['active_languages'], static::get_languages_array())
		) {
			throw new ExitException(400);
		}
		$Config                           = Config::instance();
		$Config->core['language']         = $data['language'];
		$Config->core['active_languages'] = $data['active_languages'];
		$Config->core['multilingual']     = (int)(bool)$data['multilingual'];
	}
	/**
	 * Save language settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_languages_save_settings ($Request) {
		static::admin_languages_settings_common($Request);
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Cancel language settings
	 *
	 * @throws ExitException
	 */
	static function admin_languages_cancel_settings () {
		if (!Config::instance()->cancel()) {
			throw new ExitException(500);
		}
	}
}
