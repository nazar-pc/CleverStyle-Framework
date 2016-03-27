<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller;
use
	cs\Config,
	cs\ExitException,
	cs\Language,
	cs\Language\Prefix,
	cs\Mail,
	cs\Page,
	cs\Session,
	cs\User;

trait user_ {
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function user_change_password ($Request) {
		$L    = new Prefix('system_profile_');
		$User = User::instance();
		$data = $Request->data('current_password', 'new_password');
		if (!$data) {
			throw new ExitException(400);
		}
		if (!$User->user()) {
			throw new ExitException(403);
		}
		if (!$data['new_password']) {
			throw new ExitException($L->please_type_new_password, 400);
		}
		if (!$User->validate_password($data['current_password'], $User->id, true)) {
			throw new ExitException($L->wrong_current_password, 400);
		}
		$id = $User->id;
		if ($User->set_password($data['new_password'], $id, true)) {
			Session::instance()->add($id);
		} else {
			throw new ExitException($L->change_password_server_error, 400);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 * @throws \phpmailerException
	 */
	static function user_registration ($Request) {
		$Config = Config::instance();
		$L      = new Prefix('system_profile_registration_');
		$User   = User::instance();
		if (!$User->guest()) {
			throw new ExitException(403);
		}
		if (!$Config->core['allow_user_registration']) {
			throw new ExitException($L->prohibited, 403);
		}
		$email = $Request->data('email');
		if (!$email) {
			throw new ExitException($L->please_type_your_email, 400);
		}
		$email  = mb_strtolower($email);
		$result = $User->registration($email);
		if ($result === false) {
			throw new ExitException($L->please_type_correct_email, 400);
		}
		if ($result == 'error') {
			throw new ExitException($L->server_error, 500);
		}
		if ($result == 'exists') {
			throw new ExitException($L->error_exists, 400);
		}
		$confirm = $result['reg_key'] !== true;
		if ($Request->data('username')) {
			$User->set('username', $Request->data['username'], $result['id']);
		}
		// Actually `sha512(sha512(password) + public_key)` instead of plain password
		if ($Request->data('password')) {
			$User->set_password($Request->data['password'], $result['id'], true);
		}
		if ($Request->data('language')) {
			$User->set('language', $Request->data['language'], $result['id']);
		}
		if ($Request->data('timezone')) {
			$User->set('timezone', $Request->data['timezone'], $result['id']);
		}
		if ($Request->data('avatar')) {
			$User->set('avatar', $Request->data['avatar'], $result['id']);
		}
		if ($confirm) {
			$body = $L->need_confirmation_mail_body(
				$User->username($result['id']),
				get_core_ml_text('name'),
				$Config->core_url()."/profile/registration_confirmation/$result[reg_key]",
				$L->time($Config->core['registration_confirmation_time'], 'd')
			);
		} elseif ($result['password']) {
			$body = $L->success_mail_with_password_body(
				$User->username($result['id']),
				get_core_ml_text('name'),
				$Config->core_url().'/profile/settings',
				$User->get('login', $result['id']),
				$result['password']
			);
		} else {
			$body = $L->success_mail(
				$User->username($result['id']),
				get_core_ml_text('name'),
				$Config->core_url().'/profile/settings',
				$User->get('login', $result['id'])
			);
		}
		if (!Mail::instance()->send_to(
			$email,
			$L->{$confirm ? 'need_confirmation_mail' : 'success_mail'}(get_core_ml_text('name')),
			$body
		)
		) {
			$User->registration_cancel();
			throw new ExitException($L->mail_sending_error, 500);
		}
		// TODO: better implement using 2 status codes: 202 if confirmation needed and 201 otherwise
		Page::instance()->json($confirm ? 'registration_confirmation' : 'registration_success');
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 * @throws \phpmailerException
	 */
	static function user_restore_password ($Request) {
		$Config = Config::instance();
		$L      = new Prefix('system_profile_restore_password_');
		$User   = User::instance();
		$email  = $Request->data('email');
		if (!$User->guest()) {
			throw new ExitException(403);
		}
		if (!$email) {
			throw new ExitException($L->please_type_your_email, 400);
		}
		$id = $User->get_id(mb_strtolower($email));
		if (!$id) {
			throw new ExitException($L->user_with_such_login_email_not_found, 400);
		}
		$key = $User->restore_password($id);
		if (
			!$key ||
			!Mail::instance()->send_to(
				$User->get('email', $id),
				$L->confirmation_mail(get_core_ml_text('name')),
				$L->confirmation_mail_body(
					$User->username($id),
					get_core_ml_text('name'),
					$Config->core_url()."/profile/restore_password_confirmation/$key",
					$L->time($Config->core['registration_confirmation_time'], 'd')
				)
			)
		) {
			throw new ExitException($L->server_error, 500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function user_sign_in ($Request) {
		$Config = Config::instance();
		$L      = new Prefix('system_profile_sign_in_');
		$User   = User::instance();
		$data   = $Request->data('login', 'password');
		if (!$data) {
			throw new ExitException(400);
		}
		if (!$User->guest()) {
			return;
		}
		if (
			$Config->core['sign_in_attempts_block_count'] &&
			$User->get_sign_in_attempts_count($data['login']) >= $Config->core['sign_in_attempts_block_count']
		) {
			$User->sign_in_result(false, $data['login']);
			throw new ExitException($L->attempts_are_over_try_again_in(format_time($Config->core['sign_in_attempts_block_time'])), 403);
		}
		$id = $User->get_id($data['login']);
		if ($id && $User->validate_password($data['password'], $id, true)) {
			$status      = $User->get('status', $id);
			$block_until = $User->get('block_until', $id);
			if ($status == User::STATUS_NOT_ACTIVATED) {
				throw new ExitException($L->your_account_is_not_active, 403);
			}
			if ($status == User::STATUS_INACTIVE) {
				throw new ExitException($L->your_account_disabled, 403);
			}
			if ($block_until > time()) {
				throw new ExitException($L->your_account_blocked_until(date($L->_datetime, $block_until)), 403);
			}
			Session::instance()->add($id);
			$User->sign_in_result(true, $data['login']);
		} else {
			$User->sign_in_result(false, $data['login']);
			$content = $L->authentication_error;
			if (
				$Config->core['sign_in_attempts_block_count'] &&
				$User->get_sign_in_attempts_count($data['login']) >= $Config->core['sign_in_attempts_block_count'] * 2 / 3
			) {
				$content .= ' '.$L->attempts_left($Config->core['sign_in_attempts_block_count'] - $User->get_sign_in_attempts_count($data['login']));
			}
			throw new ExitException($content, 400);
		}
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @throws ExitException
	 */
	static function user_sign_out (
		/** @noinspection PhpUnusedParameterInspection */
		$Request,
		$Response
	) {
		if (User::instance()->guest()) {
			return;
		}
		if (!Session::instance()->del()) {
			throw new ExitException(500);
		}
		/**
		 * Hack for 403 after sign out in administration
		 */
		$Response->cookie('sign_out', 1, TIME + 5, true);
	}
}
