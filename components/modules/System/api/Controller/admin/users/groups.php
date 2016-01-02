<?php
/**
 * @package    CleverStyle CMS
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
	cs\Page,
	cs\User;
trait groups {
	/**
	 * Get user's groups
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_users_groups_get ($route_ids) {
		if (!isset($route_ids[0])) {
			throw new ExitException(400);
		}
		Page::instance()->json(
			User::instance()->get_groups($route_ids[0]) ?: []
		);
	}
	/**
	 * Get user's groups
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_users_groups_put ($route_ids) {
		if (
			!isset($route_ids[0], $_POST['groups']) ||
			$route_ids[0] == User::ROOT_ID ||
			array_diff($_POST['groups'], Group::instance()->get_all()) ||
			in_array(User::BOT_GROUP_ID, $_POST['groups'])
		) {
			throw new ExitException(400);
		}
		if (!User::instance()->set_groups($_POST['groups'], $route_ids[0])) {
			throw new ExitException(500);
		}
	}
}
