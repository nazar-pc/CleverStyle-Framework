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
	cs\Page,
	cs\User;

trait profile {
	static function profile___get () {
		$User = User::instance();
		if ($User->guest()) {
			throw new ExitException(403);
		}
		$fields = [
			'id',
			'login',
			'username',
			'language',
			'timezone',
			'avatar'
		];
		Page::instance()->json(
			$User->get($fields, $User->id)
		);
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function profile___patch ($Request) {
		$user_data = $Request->data('login', 'username', 'language', 'timezone', 'avatar');
		if (
			!$user_data ||
			!$user_data['login']
		) {
			throw new ExitException(400);
		}
		$Config = Config::instance();
		$User   = User::instance();
		if ($User->guest()) {
			throw new ExitException(403);
		}
		$user_data = xap($user_data, false);
		if (
			(
				$user_data['language'] &&
				!in_array($user_data['language'], $Config->core['active_languages'])
			) ||
			(
				$user_data['timezone'] &&
				!in_array($user_data['timezone'], get_timezones_list())
			)
		) {
			throw new ExitException(400);
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
	static function profile_contacts_get () {
		$User = User::instance();
		Page::instance()->json($User->get_contacts());
	}
}
