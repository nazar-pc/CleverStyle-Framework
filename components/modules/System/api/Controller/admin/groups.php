<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\ExitException,
	cs\Group,
	cs\Page;
trait groups {
	/**
	 * Get array of groups data or data of specific group if id specified or data of several specified groups if specified in ids query parameter
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_groups___get ($route_ids) {
		$Group = Group::instance();
		$Page  = Page::instance();
		if (isset($route_ids[0])) {
			$result = $Group->get($route_ids[0]);
		} elseif (isset($_GET['ids'])) {
			$result = $Group->get(
				explode(',', $_GET['ids'])
			);
		} else {
			$result = $Group->get(
				$Group->get_all()
			);
		}
		if (!$result) {
			throw new ExitException(404);
		}
		$Page->json($result);
	}
	/**
	 * Add new group
	 *
	 * @throws ExitException
	 */
	static function admin_groups___post () {
		if (!isset($_POST['title'], $_POST['description'])) {
			throw new ExitException(400);
		}
		if (Group::instance()->add($_POST['title'], $_POST['description'])) {
			status_code(201);
		} else {
			throw new ExitException(500);
		}
	}
	/**
	 * Update group's data
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_groups___put ($route_ids) {
		if (!isset($route_ids[0], $_POST['title'], $_POST['description'])) {
			throw new ExitException(400);
		}
		if (!Group::instance()->set($route_ids[0], $_POST['title'], $_POST['description'])) {
			throw new ExitException(500);
		}
	}
	/**
	 * Delete group
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_groups___delete ($route_ids) {
		if (!isset($route_ids[0])) {
			throw new ExitException(400);
		}
		if (!Group::instance()->del($route_ids[0])) {
			throw new ExitException(500);
		}
	}
	/**
	 * Get group's permissions
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_groups_permissions_get ($route_ids) {
		if (!isset($route_ids[0])) {
			throw new ExitException(400);
		}
		Page::instance()->json(
			Group::instance()->get_permissions($route_ids[0]) ?: []
		);
	}
	/**
	 * Update group's permissions
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_groups_permissions_put ($route_ids) {
		if (!isset($route_ids[0], $_POST['permissions'])) {
			throw new ExitException(400);
		}
		if (!Group::instance()->set_permissions($_POST['permissions'], $route_ids[0])) {
			throw new ExitException(500);
		}
	}
}
