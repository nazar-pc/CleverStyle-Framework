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

trait security {
	/**
	 * Get security settings
	 */
	static function admin_security_get_settings () {
		$Config = Config::instance();
		Page::instance()->json(
			[
				'key_expire'        => $Config->core['key_expire'],
				'gravatar_support'  => $Config->core['gravatar_support'],
				'show_tooltips'     => $Config->core['show_tooltips'],
				'simple_admin_mode' => $Config->core['simple_admin_mode'],
				'applied'           => $Config->cancel_available()
			]
		);
	}
	/**
	 * Apply security settings
	 *
	 * @throws ExitException
	 */
	static function admin_security_apply_settings () {
		static::admin_security_settings_common();
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @throws ExitException
	 */
	protected static function admin_security_settings_common () {
		if (!isset(
			$_POST['key_expire'],
			$_POST['gravatar_support']
		)
		) {
			throw new ExitException(400);
		}
		$Config                           = Config::instance();
		$Config->core['key_expire']       = (int)$_POST['key_expire'];
		$Config->core['gravatar_support'] = (int)(bool)$_POST['gravatar_support'];
	}
	/**
	 * @param string $value
	 *
	 * @return string[]
	 */
	protected static function admin_security_settings_common_multiline ($value) {
		$value = _trim(explode("\n", $value));
		if ($value[0] == '') {
			$value = [];
		}
		return $value;
	}
	/**
	 * Save security settings
	 *
	 * @throws ExitException
	 */
	static function admin_security_save_settings () {
		static::admin_security_settings_common();
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Cancel security settings
	 *
	 * @throws ExitException
	 */
	static function admin_security_cancel_settings () {
		if (!Config::instance()->cancel()) {
			throw new ExitException(500);
		}
	}
}
