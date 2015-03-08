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
	cs\Page,
	cs\User;
trait profile {
	static function profile_get () {
		$User = User::instance();
		if ($User->guest()) {
			error_code(403);
		}
	}
	static function profile_profile_get () {
		$User   = User::instance();
		$fields = [
			'id',
			'login',
			'username',
			'language',
			'timezone',
			'avatar'
		];
		$Page   = Page::instance();
		$id     = $User->id;
		$Page->json($User->get($fields, $id));
	}
	static function profile_contacts_get () {
		$User = User::instance();
		Page::instance()->json($User->get_contacts());
	}
}
