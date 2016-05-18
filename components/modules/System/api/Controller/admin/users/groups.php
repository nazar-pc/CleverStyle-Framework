<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin\users;
use
	cs\ExitException,
	cs\Group,
	cs\User;

trait groups {
	/**
	 * Get user's groups
	 *
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function admin_users_groups_get ($Request) {
		if (!isset($Request->route_ids[0])) {
			throw new ExitException(400);
		}
		return User::instance()->get_groups($Request->route_ids[0]) ?: [];
	}
	/**
	 * Get user's groups
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_users_groups_put ($Request) {
		$user_id = $Request->route_ids(0);
		$groups  = $Request->data('groups');
		if (
			!$user_id ||
			!$groups ||
			$user_id == User::ROOT_ID ||
			array_diff($groups, Group::instance()->get_all())
		) {
			throw new ExitException(400);
		}
		if (!User::instance()->set_groups($groups, $user_id)) {
			throw new ExitException(500);
		}
	}
}
