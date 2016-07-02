<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin\users;
use
	cs\Config,
	cs\ExitException;

trait general {
	/**
	 * Get general users settings
	 */
	static function admin_users_general_get_settings () {
		$Config = Config::instance();
		return [
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
			'applied'                           => $Config->cancel_available()
		];
	}
	/**
	 * Apply general users settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_users_general_apply_settings ($Request) {
		static::admin_users_general_settings_common($Request);
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected static function admin_users_general_settings_common ($Request) {
		$data = $Request->data(
			'session_expire',
			'sign_in_attempts_block_count',
			'sign_in_attempts_block_time',
			'remember_user_ip',
			'password_min_length',
			'password_min_strength',
			'allow_user_registration',
			'require_registration_confirmation',
			'registration_confirmation_time',
			'auto_sign_in_after_registration'
		);
		if (!$data) {
			throw new ExitException(400);
		}
		$Config                                            = Config::instance();
		$Config->core['session_expire']                    = (int)$data['session_expire'];
		$Config->core['sign_in_attempts_block_count']      = (int)$data['sign_in_attempts_block_count'];
		$Config->core['sign_in_attempts_block_time']       = (int)$data['sign_in_attempts_block_time'];
		$Config->core['remember_user_ip']                  = (int)(bool)$data['remember_user_ip'];
		$Config->core['password_min_length']               = (int)$data['password_min_length'];
		$Config->core['password_min_strength']             = (int)$data['password_min_strength'];
		$Config->core['allow_user_registration']           = (int)(bool)$data['allow_user_registration'];
		$Config->core['require_registration_confirmation'] = (int)(bool)$data['require_registration_confirmation'];
		$Config->core['registration_confirmation_time']    = (int)$data['registration_confirmation_time'];
		$Config->core['auto_sign_in_after_registration']   = (int)(bool)$data['auto_sign_in_after_registration'];
	}
	/**
	 * Save general users settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_users_general_save_settings ($Request) {
		static::admin_users_general_settings_common($Request);
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
		Config::instance()->cancel();
	}
}
