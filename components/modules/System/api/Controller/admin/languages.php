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
	cs\ExitException,
	cs\Page;

trait languages {
	/**
	 * Get languages settings
	 */
	static function admin_languages_get_settings () {
		$Config = Config::instance();
		Page::instance()->json(
			[
				'language'         => $Config->core['language'],
				'active_languages' => $Config->core['active_languages'],
				'languages'        => static::get_languages_array(),
				'multilingual'     => $Config->core['multilingual'],
				'applied'          => $Config->cancel_available(),
				'show_tooltips'    => $Config->core['show_tooltips']
			]
		);
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
	 * @throws ExitException
	 */
	static function admin_languages_apply_settings () {
		static::admin_languages_settings_common();
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @throws ExitException
	 */
	protected static function admin_languages_settings_common () {
		if (
			!isset($_POST['language'], $_POST['active_languages'], $_POST['multilingual']) ||
			!is_array($_POST['active_languages']) ||
			!in_array($_POST['language'], $_POST['active_languages'], true) ||
			array_diff($_POST['active_languages'], static::get_languages_array())
		) {
			throw new ExitException(400);
		}
		$Config                           = Config::instance();
		$Config->core['language']         = $_POST['language'];
		$Config->core['active_languages'] = $_POST['active_languages'];
		$Config->core['multilingual']     = (int)(bool)$_POST['multilingual'];
	}
	/**
	 * Save language settings
	 *
	 * @throws ExitException
	 */
	static function admin_languages_save_settings () {
		static::admin_languages_settings_common();
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
