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

trait site_info {
	/**
	 * Get site info settings
	 */
	static function admin_site_info_get_settings () {
		$Config = Config::instance();
		Page::instance()->json(
			[
				'site_name'         => get_core_ml_text('name'),
				'url'               => implode("\n", $Config->core['url']),
				'cookie_domain'     => implode("\n", $Config->core['cookie_domain']),
				'cookie_prefix'     => $Config->core['cookie_prefix'],
				'timezone'          => $Config->core['timezone'],
				'admin_email'       => $Config->core['admin_email'],
				'show_tooltips'     => $Config->core['show_tooltips'],
				'simple_admin_mode' => $Config->core['simple_admin_mode'],
				'applied'           => $Config->cancel_available()
			]
		);
	}
	/**
	 * Apply site info settings
	 *
	 * @throws ExitException
	 */
	static function admin_site_info_apply_settings () {
		static::admin_site_info_settings_common();
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @throws ExitException
	 */
	protected static function admin_site_info_settings_common () {
		if (
			!isset($_POST['site_name'], $_POST['url'], $_POST['cookie_domain'], $_POST['cookie_prefix'], $_POST['timezone'], $_POST['admin_email']) ||
			!in_array($_POST['timezone'], get_timezones_list(), true)
		) {
			throw new ExitException(400);
		}
		$Config                        = Config::instance();
		$Config->core['name']          = set_core_ml_text('name', xap($_POST['site_name']));
		$Config->core['url']           = static::admin_site_info_settings_common_multiline($_POST['url']);
		$Config->core['cookie_domain'] = static::admin_site_info_settings_common_multiline($_POST['cookie_domain']);
		$Config->core['cookie_prefix'] = xap($_POST['cookie_prefix']);
		$Config->core['timezone']      = $_POST['timezone'];
		$Config->core['admin_email']   = xap($_POST['admin_email'], true);
	}
	/**
	 * @param string $value
	 *
	 * @return string[]
	 */
	protected static function admin_site_info_settings_common_multiline ($value) {
		$value = _trim(explode("\n", $value));
		if ($value[0] == '') {
			$value = [];
		}
		return $value;
	}
	/**
	 * Save site info settings
	 *
	 * @throws ExitException
	 */
	static function admin_site_info_save_settings () {
		static::admin_site_info_settings_common();
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Cancel site info settings
	 *
	 * @throws ExitException
	 */
	static function admin_site_info_cancel_settings () {
		if (!Config::instance()->cancel()) {
			throw new ExitException(500);
		}
	}
}
