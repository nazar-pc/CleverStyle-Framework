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
	cs\User;

class Controller {
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 */
	static function profile_registration_confirmation ($Request, $Response) {
		$L    = Language::prefix('system_profile_registration_');
		$Page = Page::instance();
		$User = User::instance();
		if ($Request->cookie('reg_confirm')) {
			$Response->cookie('reg_confirm', '');
			$Page->title($L->success_title);
			$Page->success($L->success);
			return;
		} elseif (!$User->guest()) {
			$Page->title($L->you_are_already_registered_title);
			$Page->warning($L->you_are_already_registered);
			return;
		} elseif (!isset($Request->route[2])) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		$result = $User->registration_confirmation($Request->route[2]);
		if ($result === false) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		if ($result['password']) {
			$body = $L->success_mail_with_password_body(
				strstr($result['email'], '@', true),
				get_core_ml_text('name'),
				Config::instance()->core_url().'/profile/settings',
				$User->get('login', $result['id']),
				$result['password']
			);
		} else {
			$body = $L->success_mail_body(
				strstr($result['email'], '@', true),
				get_core_ml_text('name'),
				Config::instance()->core_url().'/profile/settings',
				$User->get('login', $result['id'])
			);
		}
		if (Mail::instance()->send_to(
			$result['email'],
			$L->success_mail(get_core_ml_text('name')),
			$body
		)
		) {
			$Response->cookie('reg_confirm', 1, 0, true);
			$Response->redirect('/System/profile/registration_confirmation');
		} else {
			$User->registration_cancel();
			$Page->title($L->mail_sending_error_title);
			$Page->warning($L->mail_sending_error);
		}
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 */
	static function profile_restore_password_confirmation ($Request, $Response) {
		$L    = Language::prefix('system_profile_restore_password_');
		$Page = Page::instance();
		$User = User::instance();
		if ($Request->cookie('restore_password_confirm')) {
			$Response->cookie('restore_password_confirm', '');
			$Page->title($L->success_title);
			$Page->success($L->success);
			return;
		} elseif (!$User->guest()) {
			$Page->title($L->you_are_already_registered_title);
			$Page->warning($L->you_are_already_registered);
			return;
		} elseif (!isset($Request->route[2])) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		$result = $User->restore_password_confirmation($Request->route[2]);
		if ($result === false) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		if (Mail::instance()->send_to(
			$User->get('email', $result['id']),
			$L->success_mail(get_core_ml_text('name')),
			$L->success_mail_body(
				$User->username($result['id']),
				get_core_ml_text('name'),
				Config::instance()->core_url().'/profile/settings',
				$User->get('login', $result['id']),
				$result['password']
			)
		)
		) {
			$Response->cookie('restore_password_confirm', 1, 0, true);
			$Response->redirect('/System/profile/restore_password_confirmation');
		} else {
			$Page->title($L->mail_sending_error_title);
			$Page->warning($L->mail_sending_error);
		}
	}
	static function robots_txt () {
		$Page            = Page::instance();
		$Page->interface = false;
		$text            = file_get_contents(__DIR__.'/robots.txt');
		Event::instance()->fire(
			'System/robots.txt',
			[
				'text' => &$text
			]
		);
		$core_url                  = Config::instance()->core_url();
		$core_url_without_protocol = explode('//', $core_url, 2)[1];
		$host                      = explode('/', $core_url_without_protocol, 2)[0];
		$Page->Content             = "{$text}Host: $host";
	}
}
