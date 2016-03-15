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
	cs\Request,
	cs\Response,
	cs\Session,
	cs\User;

trait user_ {
	static function user_change_password () {
		$L    = new Prefix('system_profile_');
		$Page = Page::instance();
		$User = User::instance();
		if (!isset($_POST['current_password'], $_POST['new_password'])) {
			throw new ExitException(400);
		}
		if (!$User->user()) {
			throw new ExitException(403);
		} elseif (!$_POST['new_password']) {
			throw new ExitException($L->please_type_new_password, 400);
		} elseif (!$User->validate_password($_POST['current_password'], $User->id, true)) {
			throw new ExitException($L->wrong_current_password, 400);
		}
		$id = $User->id;
		if ($User->set_password($_POST['new_password'], $id, true)) {
			Session::instance()->add($id);
			$Page->json('OK');
		} else {
			throw new ExitException($L->change_password_server_error, 400);
		}
	}
	static function user_registration () {
		$Config  = Config::instance();
		$L       = new Prefix('system_profile_registration_');
		$Page    = Page::instance();
		$Request = Request::instance();
		$User    = User::instance();
		if (!isset($Request->data['email'])) {
			throw new ExitException(400);
		} elseif (!$User->guest()) {
			$Page->json('reload');
			return;
		} elseif (!$Config->core['allow_user_registration']) {
			throw new ExitException($L->prohibited, 403);
		} elseif (empty($Request->data['email'])) {
			throw new ExitException($L->please_type_your_email, 400);
		}
		$email  = mb_strtolower($Request->data['email']);
		$result = $User->registration($email);
		if ($result === false) {
			throw new ExitException($L->please_type_correct_email, 400);
		} elseif ($result == 'error') {
			throw new ExitException($L->server_error, 500);
		} elseif ($result == 'exists') {
			throw new ExitException($L->error_exists, 400);
		}
		$confirm = $result['reg_key'] !== true;
		if ($Request->data['username']) {
			$User->set('username', $Request->data['username'], $result['id']);
		}
		// Actually `sha512(sha512(password) + public_key)` instead of plain password
		if ($Request->data['password']) {
			$User->set_password($Request->data['password'], $result['id'], true);
		}
		if ($Request->data['language']) {
			$User->set('language', $Request->data['language'], $result['id']);
		}
		if ($Request->data['timezone']) {
			$User->set('timezone', $Request->data['timezone'], $result['id']);
		}
		if ($Request->data['avatar']) {
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
		if (Mail::instance()->send_to(
			$email,
			$L->{$confirm ? 'need_confirmation_mail' : 'success_mail'}(get_core_ml_text('name')),
			$body
		)
		) {
			$Page->json($confirm ? 'registration_confirmation' : 'registration_success');
		} else {
			$User->registration_cancel();
			throw new ExitException($L->mail_sending_error, 500);
		}
	}
	static function user_restore_password () {
		$Config = Config::instance();
		$L      = new Prefix('system_profile_restore_password_');
		$Page   = Page::instance();
		$User   = User::instance();
		if (!isset($_POST['email'])) {
			throw new ExitException(400);
		} elseif (!$User->guest()) {
			throw new ExitException(403);
		} elseif (!$_POST['email']) {
			throw new ExitException($L->please_type_your_email, 400);
		}
		$id = $User->get_id(mb_strtolower($_POST['email']));
		if (!$id) {
			throw new ExitException($L->user_with_such_login_email_not_found, 400);
		}
		if (
			($key = $User->restore_password($id)) &&
			Mail::instance()->send_to(
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
			$Page->json('OK');
		} else {
			throw new ExitException($L->server_error, 500);
		}
	}
	static function user_sign_in () {
		$Config = Config::instance();
		$L      = new Prefix('system_profile_sign_in_');
		$User   = User::instance();
		if (!$User->guest()) {
			return;
		} elseif (
			$Config->core['sign_in_attempts_block_count'] &&
			$User->get_sign_in_attempts_count(@$_POST['login']) >= $Config->core['sign_in_attempts_block_count']
		) {
			$User->sign_in_result(false, @$_POST['login']);
			throw new ExitException($L->attempts_are_over_try_again_in(format_time($Config->core['sign_in_attempts_block_time'])), 403);
		}
		$id = $User->get_id(@$_POST['login']);
		if (
			$id &&
			$User->validate_password(@$_POST['password'], $id, true)
		) {
			$status      = $User->get('status', $id);
			$block_until = $User->get('block_until', $id);
			if ($status == User::STATUS_NOT_ACTIVATED) {
				throw new ExitException($L->your_account_is_not_active, 403);
			} elseif ($status == User::STATUS_INACTIVE) {
				throw new ExitException($L->your_account_disabled, 403);
			} elseif ($block_until > time()) {
				throw new ExitException($L->your_account_blocked_until(date($L->_datetime, $block_until)), 403);
			}
			Session::instance()->add($id);
			$User->sign_in_result(true, $_POST['login']);
		} else {
			$User->sign_in_result(false, @$_POST['login']);
			$content = $L->authentication_error;
			if (
				$Config->core['sign_in_attempts_block_count'] &&
				$User->get_sign_in_attempts_count(@$_POST['login']) >= $Config->core['sign_in_attempts_block_count'] * 2 / 3
			) {
				$content .= ' '.$L->attempts_left($Config->core['sign_in_attempts_block_count'] - $User->get_sign_in_attempts_count(@$_POST['login']));
			}
			throw new ExitException($content, 400);
		}
	}
	static function user_sign_out () {
		$User = User::instance();
		if ($User->guest()) {
			Page::instance()->json(1);
			return;
		}
		if (isset($_POST['sign_out'])) {
			Session::instance()->del();
			/**
			 * Hack for 403 after sign out in administration
			 */
			Response::instance()->cookie('sign_out', 1, TIME + 5, true);
			Page::instance()->json(1);
		}
	}
}
