<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System;
use
	cs\Config,
	cs\Event,
	cs\Language,
	cs\Mail,
	cs\Page,
	cs\Route,
	cs\User;
class Controller {
	static function profile () {
		$rc       = &Route::instance()->route;
		$subparts = file_get_json(__DIR__.'/index.json')[$rc[0]];
		$User     = User::instance();
		if (
			(
				!isset($rc[1]) && $User->user()
			) ||
			(
				isset($rc[1]) && !in_array($rc[1], $subparts)
			)
		) {
			if (isset($rc[1])) {
				$rc[2] = $rc[1];
			} else {
				$rc[2] = $User->login;
			}
			$rc[1] = $subparts[0];
		}
	}
	static function profile_registration_confirmation () {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$Route  = Route::instance();
		$User   = User::instance();
		if (_getcookie('reg_confirm')) {
			_setcookie('reg_confirm', '');
			$Page->title($L->reg_success_title);
			$Page->success($L->reg_success);
			return;
		} elseif (!$User->guest()) {
			$Page->title($L->you_are_already_registered_title);
			$Page->warning($L->you_are_already_registered);
			return;
		} elseif (!isset($Route->route[2])) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		$result = $User->registration_confirmation($Route->route[2]);
		if ($result === false) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		$body = $L->reg_success_mail_body(
			strstr($result['email'], '@', true),
			get_core_ml_text('name'),
			$Config->core_url().'/profile/settings',
			$User->get('login', $result['id']),
			$result['password']
		);
		if (Mail::instance()->send_to(
			$result['email'],
			$L->reg_success_mail(get_core_ml_text('name')),
			$body
		)
		) {
			_setcookie('reg_confirm', 1, 0, true);
			_header("Location: {$Config->base_url()}/System/profile/registration_confirmation");
		} else {
			$User->registration_cancel();
			$Page->title($L->sending_reg_mail_error_title);
			$Page->warning($L->sending_reg_mail_error);
		}
	}
	static function profile_restore_password_confirmation () {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$Route  = Route::instance();
		$User   = User::instance();
		if (_getcookie('restore_password_confirm')) {
			_setcookie('restore_password_confirm', '');
			$Page->title($L->restore_password_success_title);
			$Page->success($L->restore_password_success);
			return;
		} elseif (!$User->guest()) {
			$Page->title($L->you_are_already_registered_title);
			$Page->warning($L->you_are_already_registered);
			return;
		} elseif (!isset($Route->route[2])) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		$result = $User->restore_password_confirmation($Route->route[2]);
		if ($result === false) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		if (Mail::instance()->send_to(
			$User->get('email', $result['id']),
			$L->restore_password_success_mail(get_core_ml_text('name')),
			$L->restore_password_success_mail_body(
				$User->username($result['id']),
				get_core_ml_text('name'),
				$Config->core_url().'/profile/settings',
				$User->get('login', $result['id']),
				$result['password']
			)
		)
		) {
			_setcookie('restore_password_confirm', 1, 0, true);
			_header("Location: {$Config->base_url()}/System/profile/restore_password_confirmation");
		} else {
			$Page->title($L->sending_reg_mail_error_title);
			$Page->warning($L->sending_reg_mail_error);
		}
	}
	static function robots_txt () {
		interface_off();
		$text = file_get_contents(__DIR__.'/robots.txt');
		Event::instance()->fire(
			'System/robots.txt',
			[
				'text' => &$text
			]
		);
		$core_url                  = Config::instance()->core_url();
		$core_url_without_protocol = explode('//', $core_url, 2)[1];
		$host                      = explode('/', $core_url_without_protocol, 2)[0];
		Page::instance()->Content  = "{$text}Host: $host";
	}
}
