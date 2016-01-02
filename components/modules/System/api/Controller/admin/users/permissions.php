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
	cs\Page,
	cs\User;
trait permissions {
	/**
	 * Get user's permissions
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_users_permissions_get ($route_ids) {
		if (!isset($route_ids[0])) {
			throw new ExitException(400);
		}
		Page::instance()->json(
			User::instance()->get_permissions($route_ids[0]) ?: []
		);
	}
	/**
	 * Update user's permissions
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_users_permissions_put ($route_ids) {
		if (!isset($route_ids[0], $_POST['permissions'])) {
			throw new ExitException(400);
		}
		if (!User::instance()->set_permissions($_POST['permissions'], $route_ids[0])) {
			throw new ExitException(500);
		}
	}
}
