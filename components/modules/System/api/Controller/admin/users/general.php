<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin\users;
use
	cs\Config,
	cs\ExitException,
	cs\Page;

trait general {
	/**
	 * Get general users settings
	 */
	static function admin_users_general_get_settings () {
		$Config = Config::instance();
		Page::instance()->json(
			[
				'session_expire'                    => $Config->core['session_expire'],
				'sign_in_attempts_block_count'      => $Config->core['sign_in_attempts_block_count'],
				'sign_in_attempts_block_time'       => $Config->core['sign_in_attempts_block_time'],
				'remember_user_ip'                  => $Config->core['remember_user_ip'],
				'password_min_length'               => $Config->core['password_min_length'],
				'password_min_strength'             => $Config->core['password_min_strength'],
				'allow_user_registration'           => $Config->core['allow_user_registration'],
				'require_registration_confirmation' => $Config->core['require_registration_confirmation'],
				'registration_confirmation_time'    => $Config->core['registration_confirmation_time'],
				'auto_sign_in_after_registration'   => $Config->core['auto_sign_in_after_registration'],
				'rules'                             => get_core_ml_text('rules'),
				'show_tooltips'                     => $Config->core['show_tooltips'],
				'simple_admin_mode'                 => $Config->core['simple_admin_mode'],
				'applied'                           => $Config->cancel_available()
			]
		);
	}
	/**
	 * Apply general users settings
	 *
	 * @throws ExitException
	 */
	static function admin_users_general_apply_settings () {
		static::admin_users_general_settings_common();
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @throws ExitException
	 */
	protected static function admin_users_general_settings_common () {
		if (!isset(
			$_POST['session_expire'],
			$_POST['sign_in_attempts_block_count'],
			$_POST['sign_in_attempts_block_time'],
			$_POST['remember_user_ip'],
			$_POST['password_min_length'],
			$_POST['password_min_strength'],
			$_POST['allow_user_registration'],
			$_POST['require_registration_confirmation'],
			$_POST['registration_confirmation_time'],
			$_POST['auto_sign_in_after_registration'],
			$_POST['rules']
		)
		) {
			throw new ExitException(400);
		}
		$Config                                            = Config::instance();
		$Config->core['session_expire']                    = (int)$_POST['session_expire'];
		$Config->core['sign_in_attempts_block_count']      = (int)$_POST['sign_in_attempts_block_count'];
		$Config->core['sign_in_attempts_block_time']       = (int)$_POST['sign_in_attempts_block_time'];
		$Config->core['remember_user_ip']                  = (int)(bool)$_POST['remember_user_ip'];
		$Config->core['password_min_length']               = (int)$_POST['password_min_length'];
		$Config->core['password_min_strength']             = (int)$_POST['password_min_strength'];
		$Config->core['allow_user_registration']           = (int)(bool)$_POST['allow_user_registration'];
		$Config->core['require_registration_confirmation'] = (int)(bool)$_POST['require_registration_confirmation'];
		$Config->core['registration_confirmation_time']    = (int)$_POST['registration_confirmation_time'];
		$Config->core['auto_sign_in_after_registration']   = (int)(bool)$_POST['auto_sign_in_after_registration'];
		$Config->core['rules']                             = set_core_ml_text('rules', xap($_POST['rules'], true));
	}
	/**
	 * Save general users settings
	 *
	 * @throws ExitException
	 */
	static function admin_users_general_save_settings () {
		static::admin_users_general_settings_common();
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Cancel general users settings
	 *
	 * @throws ExitException
	 */
	static function admin_users_general_cancel_settings () {
		if (!Config::instance()->cancel()) {
			throw new ExitException(500);
		}
	}
}
