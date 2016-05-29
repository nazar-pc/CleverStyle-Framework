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
	cs\ExitException;

trait site_info {
	/**
	 * Get site info settings
	 */
	static function admin_site_info_get_settings () {
		$Config = Config::instance();
		return [
			'site_name'     => get_core_ml_text('name'),
			'url'           => implode("\n", $Config->core['url']),
			'cookie_domain' => implode("\n", $Config->core['cookie_domain']),
			'cookie_prefix' => $Config->core['cookie_prefix'],
			'timezone'      => $Config->core['timezone'],
			'admin_email'   => $Config->core['admin_email'],
			'applied'       => $Config->cancel_available()
		];
	}
	/**
	 * Apply site info settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_site_info_apply_settings ($Request) {
		static::admin_site_info_settings_common($Request);
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected static function admin_site_info_settings_common ($Request) {
		$data = $Request->data('site_name', 'url', 'cookie_domain', 'cookie_prefix', 'timezone', 'admin_email');
		if (!$data || !in_array($data['timezone'], get_timezones_list(), true)) {
			throw new ExitException(400);
		}
		$Config                        = Config::instance();
		$Config->core['name']          = set_core_ml_text('name', xap($data['site_name']));
		$Config->core['url']           = static::admin_site_info_settings_common_multiline($data['url']);
		$Config->core['cookie_domain'] = static::admin_site_info_settings_common_multiline($data['cookie_domain']);
		$Config->core['cookie_prefix'] = xap($data['cookie_prefix']);
		$Config->core['timezone']      = $data['timezone'];
		$Config->core['admin_email']   = xap($data['admin_email'], true);
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
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_site_info_save_settings ($Request) {
		static::admin_site_info_settings_common($Request);
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
		Config::instance()->cancel();
	}
}
