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
	cs\Mail as System_mail,
	cs\Page;

trait mail {
	/**
	 * Get mail settings
	 */
	static function admin_mail_get_settings () {
		$Config = Config::instance();
		Page::instance()->json(
			[
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
				'show_tooltips'     => $Config->core['show_tooltips'],
				'simple_admin_mode' => $Config->core['simple_admin_mode'],
				'applied'           => $Config->cancel_available()
			]
		);
	}
	/**
	 * Send test email to check if setup is correct
	 *
	 * @throws ExitException
	 */
	static function admin_mail_send_test_email () {
		if (!isset($_POST['email'])) {
			throw new ExitException(400);
		}
		if (!System_mail::instance()->send_to($_GET['email'], 'Email testing on '.get_core_ml_text('name'), 'Test email')) {
			throw new ExitException(500);
		}
	}
	/**
	 * Apply mail settings
	 *
	 * @throws ExitException
	 */
	static function admin_mail_apply_settings () {
		static::admin_mail_settings_common();
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @throws ExitException
	 */
	protected static function admin_mail_settings_common () {
		if (
			!isset(
				$_POST['smtp'],
				$_POST['smtp_host'],
				$_POST['smtp_port'],
				$_POST['smtp_secure'],
				$_POST['smtp_auth'],
				$_POST['smtp_user'],
				$_POST['smtp_password'],
				$_POST['mail_from'],
				$_POST['mail_from_name'],
				$_POST['mail_signature']
			) ||
			!in_array($_POST['smtp_secure'], ['', 'ssl', 'tls'], true)
		) {
			throw new ExitException(400);
		}
		$Config                         = Config::instance();
		$Config->core['smtp']           = (int)(bool)$_POST['smtp'];
		$Config->core['smtp_host']      = $_POST['smtp_host'];
		$Config->core['smtp_port']      = (int)$_POST['smtp_port'];
		$Config->core['smtp_secure']    = $_POST['smtp_secure'];
		$Config->core['smtp_auth']      = (int)(bool)$_POST['smtp_auth'];
		$Config->core['smtp_user']      = $_POST['smtp_user'];
		$Config->core['smtp_password']  = $_POST['smtp_password'];
		$Config->core['mail_from']      = $_POST['mail_from'];
		$Config->core['mail_from_name'] = set_core_ml_text('mail_from_name', xap($_POST['mail_from_name']));
		$Config->core['mail_signature'] = set_core_ml_text('mail_signature', xap($_POST['mail_signature'], true));
	}
	/**
	 * Save mail settings
	 *
	 * @throws ExitException
	 */
	static function admin_mail_save_settings () {
		static::admin_mail_settings_common();
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
