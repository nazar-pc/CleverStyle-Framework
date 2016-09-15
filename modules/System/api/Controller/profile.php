<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller;
use
	cs\Config,
	cs\Core,
	cs\ExitException,
	cs\Language,
	cs\Mail,
	cs\Session,
	cs\User;

trait profile {
	public static function profile_get () {
		$User         = User::instance();
		$fields       = [
			'id',
			'login',
			'username',
			'language',
			'timezone',
			'avatar'
		];
		$result       = $User->get($fields, $User->id);
		$result['id'] = (int)$result['id'];
		if ($User->guest()) {
			$result['username'] = Language::instance()->system_profile_guest;
			$result['avatar']   = $User->avatar();
		}
		return $result;
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function profile_patch ($Request) {
		$user_data = $Request->data('login', 'username', 'language', 'timezone', 'avatar');
		if (
			!$user_data ||
			!$user_data['login']
		) {
			throw new ExitException(400);
		}
		$User = User::instance();
		if ($User->guest()) {
			throw new ExitException(403);
		}
		$user_data['login'] = mb_strtolower($user_data['login']);
		/**
		 * Check for changing login to new one and whether it is available
		 */
		if (
			$user_data['login'] != $User->login &&
			$user_data['login'] != $User->email &&
			(
				filter_var($user_data['login'], FILTER_VALIDATE_EMAIL) ||
				$User->get_id(hash('sha224', $user_data['login'])) !== false
			)
		) {
			throw new ExitException(Language::instance()->system_admin_users_login_occupied, 400);
		}
		if (!$User->set($user_data)) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function profile_change_password ($Request) {
		$data = $Request->data('current_password', 'new_password');
		if (!$data) {
			throw new ExitException(400);
		}
		$User = User::instance();
		if (!$User->user()) {
			throw new ExitException(403);
		}
		$L = Language::prefix('system_profile_');
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
			throw new ExitException($L->change_password_server_error, 500);
		}
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @throws ExitException
	 */
	public static function profile_registration ($Request, $Response) {
		$Config = Config::instance();
		$L      = Language::prefix('system_profile_registration_');
		$User   = User::instance();
		if (!$User->guest()) {
			throw new ExitException(403);
		}
		if (!$Config->core['allow_user_registration']) {
			throw new ExitException($L->prohibited, 403);
		}
		$email   = $Request->data('email');
		$email   = mb_strtolower($email);
		$result  = static::try_to_register($User, $L, $email);
		$confirm = $result['reg_key'] !== true;
		static::fill_optional_profile_data($Request, $User, $result['id']);
		$title = $L->success_mail($Config->core['site_name']);
		$body  = $L->success_mail(
			$User->username($result['id']),
			$Config->core['site_name'],
			$Config->core_url().'/profile/settings',
			$User->get('login', $result['id'])
		);
		if ($confirm) {
			$title = $L->need_confirmation_mail($Config->core['site_name']);
			$body  = $L->need_confirmation_mail_body(
				$User->username($result['id']),
				$Config->core['site_name'],
				$Config->core_url()."/profile/registration_confirmation/$result[reg_key]",
				$L->time($Config->core['registration_confirmation_time'], 'd')
			);
		} elseif (!$Request->data('password') && $result['password']) {
			$body = $L->success_mail_with_password_body(
				$User->username($result['id']),
				$Config->core['site_name'],
				$Config->core_url().'/profile/settings',
				$User->get('login', $result['id']),
				$result['password']
			);
		}
		if (!Mail::instance()->send_to($email, $title, $body)) {
			$User->registration_cancel();
			throw new ExitException($L->mail_sending_error, 500);
		}
		$Response->code = $confirm ? 202 : 201;
	}
	/**
	 * @param User            $User
	 * @param Language\Prefix $L
	 * @param string          $email
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	protected static function try_to_register ($User, $L, $email) {
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
		return $result;
	}
	/**
	 * @param \cs\Request $Request
	 * @param User        $User
	 * @param int         $user_id
	 */
	protected static function fill_optional_profile_data ($Request, $User, $user_id) {
		if ($Request->data('username')) {
			$User->set('username', $Request->data['username'], $user_id);
		}
		// Actually `sha512(sha512(password) + public_key)` instead of plain password
		if ($Request->data('password')) {
			$User->set_password($Request->data['password'], $user_id, true);
		}
		if ($Request->data('language')) {
			$User->set('language', $Request->data['language'], $user_id);
		}
		if ($Request->data('timezone')) {
			$User->set('timezone', $Request->data['timezone'], $user_id);
		}
		if ($Request->data('avatar')) {
			$User->set('avatar', $Request->data['avatar'], $user_id);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function profile_restore_password ($Request) {
		$User = User::instance();
		if (!$User->guest()) {
			throw new ExitException(403);
		}
		$L     = Language::prefix('system_profile_restore_password_');
		$email = $Request->data('email');
		if (!$email) {
			throw new ExitException($L->please_type_your_email, 400);
		}
		$id = $User->get_id(mb_strtolower($email));
		if (!$id) {
			throw new ExitException($L->user_with_such_login_email_not_found, 400);
		}
		$key    = $User->restore_password($id);
		$Config = Config::instance();
		if (
			!$key ||
			!Mail::instance()->send_to(
				$User->get('email', $id),
				$L->confirmation_mail($Config->core['site_name']),
				$L->confirmation_mail_body(
					$User->username($id),
					$Config->core['site_name'],
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
	public static function profile_sign_in ($Request) {
		$Config = Config::instance();
		$L      = Language::prefix('system_profile_sign_in_');
		$User   = User::instance();
		$data   = $Request->data('login', 'password');
		if (!$data) {
			throw new ExitException(400);
		}
		if (!$User->guest()) {
			return;
		}
		$attempts = $User->get_sign_in_attempts_count($data['login']);
		if (
			$Config->core['sign_in_attempts_block_count'] &&
			$attempts >= $Config->core['sign_in_attempts_block_count']
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
			++$attempts;
			$content = $L->authentication_error;
			if ($Config->core['sign_in_attempts_block_count']) {
				$attempts_left = $Config->core['sign_in_attempts_block_count'] - $attempts;
				if (!$attempts_left) {
					throw new ExitException($L->attempts_are_over_try_again_in(format_time($Config->core['sign_in_attempts_block_time'])), 403);
				} elseif ($attempts >= $Config->core['sign_in_attempts_block_count'] * 2 / 3) {
					$content .= ' '.$L->attempts_left($attempts_left);
				}
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
	public static function profile_sign_out (
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
		$Response->cookie('sign_out', 1, time() + 5, true);
	}
	public static function profile_configuration () {
		$Config = Config::instance();
		return [
			'public_key'            => Core::instance()->public_key,
			'password_min_length'   => (int)$Config->core['password_min_length'],
			'password_min_strength' => (int)$Config->core['password_min_strength']
		];
	}
}
