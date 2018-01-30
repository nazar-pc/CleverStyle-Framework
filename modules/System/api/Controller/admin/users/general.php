<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
namespace cs\modules\System\api\Controller\admin\users;
use
	cs\Config;

trait general {
	protected static $users_general_options_keys = [
		'session_expire',
		'remember_user_ip',
		'password_min_length',
		'password_min_strength',
		'allow_user_registration',
		'require_registration_confirmation',
		'registration_confirmation_time',
		'auto_sign_in_after_registration'
	];
	/**
	 * Get general users settings
	 *
	 * @return array
	 */
	public static function admin_users_general_get_settings () {
		$Config = Config::instance();
		return $Config->core(static::$users_general_options_keys) + [
			'applied' => $Config->cancel_available()
		];
	}
	/**
	 * Apply general users settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_users_general_apply_settings ($Request) {
		static::admin_core_options_apply($Request, static::$users_general_options_keys);
	}
	/**
	 * Save general users settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_users_general_save_settings ($Request) {
		static::admin_core_options_save($Request, static::$users_general_options_keys);
	}
	/**
	 * Cancel general users settings
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_users_general_cancel_settings () {
		static::admin_core_options_cancel();
	}
}
