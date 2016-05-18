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
	cs\ExitException,
	cs\User;

trait profiles {
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function profiles_get ($Request) {
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
		if (!$Request->route(1)) {
			throw new ExitException('Specified ids are expected', 400);
		}
		$ids    = _int(explode(',', $Request->route[1]));
		$single = count($ids) == 1;
		$ids    = array_intersect($ids, $User->get_contacts());
		if (!$ids) {
			throw new ExitException('User is not in your contacts', 403);
		}
		return $single ? $User->get($fields, $ids[0]) : array_map(
			function ($id) use ($fields, $User) {
				$result       = $User->get($fields, $id);
				$result['id'] = (int)$result['id'];
				return $result;
			},
			$ids
		);
	}
}
