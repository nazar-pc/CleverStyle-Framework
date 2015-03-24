<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller;
use
	cs\Config,
	cs\Language,
	cs\Mail,
	cs\Page,
	cs\Session,
	cs\User;
trait user_ {
	static function user_change_password () {
		$L    = Language::instance();
		$Page = Page::instance();
		$User = User::instance();
		if (!isset($_POST['current_password'], $_POST['new_password'])) {
			error_code(400);
			return;
		}
		if (!$User->user()) {
			error_code(403);
			return;
		} elseif (!$_POST['new_password']) {
			error_code(400);
			$Page->error($L->please_type_new_password);
			return;
		} elseif (!$User->validate_password($_POST['current_password'], $User->id, true)) {
			error_code(400);
			$Page->error($L->wrong_current_password);
			return;
		}
		$id = $User->id;
		if ($User->set_password($_POST['new_password'], $id, true)) {
			Session::instance()->add($id);
			$Page->json('OK');
		} else {
			error_code(400);
			$Page->error($L->change_password_server_error);
		}
	}
	static function user_registration () {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$User   = User::instance();
		if (!isset($_POST['email'])) {
			error_code(400);
			return;
		} elseif (!$User->guest()) {
			$Page->json('reload');
			return;
		} elseif (!$Config->core['allow_user_registration']) {
			error_code(403);
			$Page->error($L->registration_prohibited);
			return;
		} elseif (empty($_POST['email'])) {
			error_code(400);
			$Page->error($L->please_type_your_email);
			return;
		}
		$_POST['email'] = mb_strtolower($_POST['email']);
		$result         = $User->registration($_POST['email']);
		if ($result === false) {
			error_code(400);
			$Page->error($L->please_type_correct_email);
			return;
		} elseif ($result == 'error') {
			error_code(500);
			$Page->error($L->reg_server_error);
			return;
		} elseif ($result == 'exists') {
			error_code(400);
			$Page->error($L->reg_error_exists);
			return;
		}
		$confirm = $result['reg_key'] !== true;
		if ($confirm) {
			$body = $L->reg_need_confirmation_mail_body(
				strstr($_POST['email'], '@', true),
				get_core_ml_text('name'),
				$Config->core_url()."/profile/registration_confirmation/$result[reg_key]",
				$L->time($Config->core['registration_confirmation_time'], 'd')
			);
		} else {
			$body = $L->reg_success_mail_body(
				strstr($_POST['email'], '@', true),
				get_core_ml_text('name'),
				$Config->core_url().'/profile/settings',
				$User->get('login', $result['id']),
				$result['password']
			);
		}
		if (Mail::instance()->send_to(
			$_POST['email'],
			$L->{$confirm ? 'reg_need_confirmation_mail' : 'reg_success_mail'}(get_core_ml_text('name')),
			$body
		)
		) {
			$Page->json($confirm ? 'reg_confirmation' : 'reg_success');
		} else {
			$User->registration_cancel();
			error_code(500);
			$Page->error($L->sending_reg_mail_error);
		}
	}
	static function user_restore_password () {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$User   = User::instance();
		if (!isset($_POST['email'])) {
			error_code(400);
			return;
		} elseif (!$User->guest()) {
			error_code(403);
			return;
		} elseif (!$_POST['email']) {
			error_code(400);
			$Page->error($L->please_type_your_email);
			return;
		} elseif (!($id = $User->get_id(mb_strtolower($_POST['email'])))) {
			error_code(400);
			$Page->error($L->user_with_such_login_email_not_found);
			return;
		}
		if (
			($key = $User->restore_password($id)) &&
			Mail::instance()->send_to(
				$User->get('email', $id),
				$L->restore_password_confirmation_mail(get_core_ml_text('name')),
				$L->restore_password_confirmation_mail_body(
					$User->username($id),
					get_core_ml_text('name'),
					$Config->core_url()."/profile/restore_password_confirmation/$key",
					$L->time($Config->core['registration_confirmation_time'], 'd')
				)
			)
		) {
			$Page->json('OK');
		} else {
			error_code(500);
			$Page->error($L->restore_password_server_error);
		}
	}
	static function user_sign_in () {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$User   = User::instance();
		if (!$User->guest()) {
			return;
		} elseif ($Config->core['sign_in_attempts_block_count'] && $User->get_sign_in_attempts_count() >= $Config->core['sign_in_attempts_block_count']) {
			$User->sign_in_result(false);
			error_code(403);
			$Page->error("$L->sign_in_attempts_ends_try_after ".format_time($Config->core['sign_in_attempts_block_time']));
			return;
		}
		$id = $User->get_id(@$_POST['login']);
		if (
			$id &&
			$User->validate_password(@$_POST['password'], $id, true)
		) {
			if ($User->get('status', $id) == User::STATUS_NOT_ACTIVATED) {
				error_code(403);
				$Page->error($L->your_account_is_not_active);
				return;
			} elseif ($User->get('status', $id) == User::STATUS_INACTIVE) {
				error_code(403);
				$Page->error($L->your_account_disabled);
				return;
			}
			Session::instance()->add($id);
			$User->sign_in_result(true);
		} else {
			$User->sign_in_result(false);
			error_code(400);
			$content = $L->auth_error_sign_in;
			if (
				$Config->core['sign_in_attempts_block_count'] &&
				$User->get_sign_in_attempts_count() >= $Config->core['sign_in_attempts_block_count'] * 2 / 3
			) {
				$content .= " $L->sign_in_attempts_left ".($Config->core['sign_in_attempts_block_count'] - $User->get_sign_in_attempts_count());
			}
			$Page->error($content);
			unset($content);
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
			_setcookie('sign_out', 1, TIME + 5, true);
			Page::instance()->json(1);
		}
	}
}
