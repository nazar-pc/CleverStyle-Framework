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
	cs\Mail as System_mail;

trait mail {
	/**
	 * Get mail settings
	 */
	static function admin_mail_get_settings () {
		$Config = Config::instance();
		return [
			'smtp'              => $Config->core['smtp'],
			'smtp_host'         => $Config->core['smtp_host'],
			'smtp_port'         => $Config->core['smtp_port'],
			'smtp_secure'       => $Config->core['smtp_secure'],
			'smtp_auth'         => $Config->core['smtp_auth'],
			'smtp_user'         => $Config->core['smtp_user'],
			'smtp_password'     => $Config->core['smtp_password'],
			'mail_from'         => $Config->core['mail_from'],
			'mail_from_name'    => get_core_ml_text('mail_from_name'),
			'mail_signature'    => get_core_ml_text('mail_signature'),
			'simple_admin_mode' => $Config->core['simple_admin_mode'],
			'applied'           => $Config->cancel_available()
		];
	}
	/**
	 * Send test email to check if setup is correct
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_mail_send_test_email ($Request) {
		$email = $Request->data('email');
		if (!$email) {
			throw new ExitException(400);
		}
		if (!System_mail::instance()->send_to($email, 'Email testing on '.get_core_ml_text('name'), 'Test email')) {
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
	static function admin_mail_apply_settings ($Request) {
		static::admin_mail_settings_common($Request);
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected static function admin_mail_settings_common ($Request) {
		$data = $Request->data(
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
		);
		if (!$data || !in_array($data['smtp_secure'], ['', 'ssl', 'tls'], true)) {
			throw new ExitException(400);
		}
		$Config                         = Config::instance();
		$Config->core['smtp']           = (int)(bool)$data['smtp'];
		$Config->core['smtp_host']      = $data['smtp_host'];
		$Config->core['smtp_port']      = (int)$data['smtp_port'];
		$Config->core['smtp_secure']    = $data['smtp_secure'];
		$Config->core['smtp_auth']      = (int)(bool)$data['smtp_auth'];
		$Config->core['smtp_user']      = $data['smtp_user'];
		$Config->core['smtp_password']  = $data['smtp_password'];
		$Config->core['mail_from']      = $data['mail_from'];
		$Config->core['mail_from_name'] = set_core_ml_text('mail_from_name', xap($data['mail_from_name']));
		$Config->core['mail_signature'] = set_core_ml_text('mail_signature', xap($data['mail_signature'], true));
	}
	/**
	 * Save mail settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_mail_save_settings ($Request) {
		static::admin_mail_settings_common($Request);
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Cancel mail settings
	 *
	 * @throws ExitException
	 */
	static function admin_mail_cancel_settings () {
		if (!Config::instance()->cancel()) {
			throw new ExitException(500);
		}
	}
}
