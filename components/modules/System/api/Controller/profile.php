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
	static function profile___patch () {
		if (
			!isset($_POST['login'], $_POST['username'], $_POST['language'], $_POST['timezone'], $_POST['avatar']) ||
			!$_POST['login']
		) {
			throw new ExitException(400);
		}
		$Config = Config::instance();
		$L      = Language::instance();
		$User   = User::instance();
		if ($User->guest()) {
			throw new ExitException(403);
		}
		$user_data = [
			'login'    => $_POST['login'],
			'username' => $_POST['username'],
			'language' => $_POST['language'],
			'timezone' => $_POST['timezone'],
			'avatar'   => $_POST['avatar']
		];
		$user_data = xap($user_data, false);
		if (
			(
				!in_array($user_data['timezone'], get_timezones_list()) &&
				$user_data['timezone'] !== ''
			) ||
			(
				!in_array($user_data['language'], $Config->core['active_languages']) &&
				$user_data['language'] !== ''
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
			throw new ExitException($L->login_occupied, 400);
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
