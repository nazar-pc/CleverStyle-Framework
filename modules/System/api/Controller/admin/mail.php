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
	cs\Config,
	cs\ExitException,
	cs\Mail as System_mail;

trait mail {
	protected static $mail_options_keys = [
		'smtp',
		'smtp_host',
		'smtp_port',
		'smtp_secure',
		'smtp_auth',
		'smtp_user',
		'smtp_password',
		'mail_from',
		'mail_from_name',
		'mail_signature'
	];
	/**
	 * Get mail settings
	 *
	 * @return array
	 */
	public static function admin_mail_get_settings () {
		$Config = Config::instance();
		return $Config->core(static::$mail_options_keys) + [
			'applied' => $Config->cancel_available()
		];
	}
	/**
	 * Send test email to check if setup is correct
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_mail_send_test_email ($Request) {
		$email = $Request->data('email');
		if (!$email) {
			throw new ExitException(400);
		}
		if (!System_mail::instance()->send_to($email, 'Email testing on '.Config::instance()->core['site_name'], 'Test email')) {
			throw new ExitException(500);
		}
	}
	/**
	 * Apply mail settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_mail_apply_settings ($Request) {
		static::admin_core_options_apply($Request, static::$mail_options_keys);
	}
	/**
	 * Save mail settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_mail_save_settings ($Request) {
		static::admin_core_options_save($Request, static::$mail_options_keys);
	}
	/**
	 * Cancel mail settings
	 *
	 * @throws ExitException
	 */
	public static function admin_mail_cancel_settings () {
		static::admin_core_options_cancel();
	}
}
