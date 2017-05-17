<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System;
use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language,
	cs\Mail,
	cs\Page,
	cs\Session,
	cs\User;

class Controller {
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @throws ExitException
	 */
	public static function profile_registration_confirmation ($Request, $Response) {
		$L    = Language::prefix('system_profile_registration_');
		$User = User::instance();
		if (!$User->guest()) {
			static::redirect_with_notification($Response, $L->you_are_already_registered, 'notice');
		} elseif (!isset($Request->route[2])) {
			static::redirect_with_notification($Response, $L->invalid_confirmation_code, 'warning');
		}
		$result = $User->registration_confirmation($Request->route[2]);
		if ($result === false) {
			static::redirect_with_notification($Response, $L->invalid_confirmation_code, 'warning');
		}
		$Config = Config::instance();
		if ($result['password']) {
			$body = $L->success_mail_with_password_body(
				strstr($result['email'], '@', true),
				$Config->core['site_name'],
				Config::instance()->core_url().'/profile/settings',
				$User->get('login', $result['id']),
				$result['password']
			);
		} else {
			$body = $L->success_mail_body(
				strstr($result['email'], '@', true),
				$Config->core['site_name'],
				Config::instance()->core_url().'/profile/settings',
				$User->get('login', $result['id'])
			);
		}
		if (Mail::instance()->send_to(
			$result['email'],
			$L->success_mail($Config->core['site_name']),
			$body
		)
		) {
			static::redirect_with_notification($Response, $L->success, 'success');
		} else {
			$User->registration_cancel();
			static::redirect_with_notification($Response, $L->mail_sending_error, 'warning');
		}
	}
	/**
	 * @param \cs\Response $Response
	 * @param              $content
	 * @param string       $type `success`, `notice` or `warning`
	 *
	 * @throws ExitException
	 */
	protected static function redirect_with_notification ($Response, $content, $type) {
		Session::instance()->set_data('system_notification', [$content, $type]);
		$Response->redirect('/');
		throw new ExitException;
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @throws ExitException
	 */
	public static function profile_restore_password_confirmation ($Request, $Response) {
		$L    = Language::prefix('system_profile_restore_password_');
		$User = User::instance();
		if (!$User->guest()) {
			static::redirect_with_notification($Response, $L->you_are_already_registered, 'notice');
		} elseif (!isset($Request->route[2])) {
			static::redirect_with_notification($Response, $L->invalid_confirmation_code, 'warning');
		}
		$result = $User->restore_password_confirmation($Request->route[2]);
		if ($result === false) {
			static::redirect_with_notification($Response, $L->invalid_confirmation_code, 'warning');
		}
		$Config = Config::instance();
		if (Mail::instance()->send_to(
			$User->get('email', $result['id']),
			$L->success_mail($Config->core['site_name']),
			$L->success_mail_body(
				$User->username($result['id']),
				$Config->core['site_name'],
				Config::instance()->core_url().'/profile/settings',
				$User->get('login', $result['id']),
				$result['password']
			)
		)
		) {
			static::redirect_with_notification($Response, $L->success, 'success');
		} else {
			static::redirect_with_notification($Response, $L->mail_sending_error, 'warning');
		}
	}
	public static function robots_txt () {
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
