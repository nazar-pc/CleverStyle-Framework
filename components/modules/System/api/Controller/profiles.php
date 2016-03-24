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
	cs\ExitException,
	cs\Page,
	cs\Request,
	cs\User;

trait profiles {
	static function profiles_get () {
		$User = User::instance();
		if ($User->guest()) {
			throw new ExitException(403);
		}
		$fields  = [
			'id',
			'login',
			'username',
			'language',
			'timezone',
			'avatar'
		];
		$Page    = Page::instance();
		$Request = Request::instance();
		if (isset($Request->route[1])) {
			$id     = _int(explode(',', $Request->route[1]));
			$single = count($id) == 1;
			if (
				!$User->admin() &&
				!(
				$id = array_intersect($id, $User->get_contacts())
				)
			) {
				throw new ExitException('User is not in your contacts', 403);
			}
			if ($single) {
				$Page->json($User->get($fields, $id[0]));
			} else {
				$Page->json(
					array_map(
						function ($id) use ($fields, $User) {
							return $User->get($fields, $id);
						},
						$id
					)
				);
			}
		} else {
			throw new ExitException('Specified ids are expected', 400);
		}
	}
}
