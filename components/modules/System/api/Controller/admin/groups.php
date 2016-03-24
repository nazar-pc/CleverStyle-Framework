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
	cs\Page,
	cs\Response;

trait groups {
	/**
	 * Get array of groups data or data of specific group if id specified or data of several specified groups if specified in ids query parameter
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_groups___get ($Request) {
		$Group = Group::instance();
		$Page  = Page::instance();
		if (isset($Request->route_ids[0])) {
			$result = $Group->get($Request->route_ids[0]);
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
			Response::instance()->code = 201;
		} else {
			throw new ExitException(500);
		}
	}
	/**
	 * Update group's data
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_groups___put ($Request) {
		if (!isset($Request->route_ids[0], $_POST['title'], $_POST['description'])) {
			throw new ExitException(400);
		}
		if (!Group::instance()->set($Request->route_ids[0], $_POST['title'], $_POST['description'])) {
			throw new ExitException(500);
		}
	}
	/**
	 * Delete group
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_groups___delete ($Request) {
		if (!isset($Request->route_ids[0])) {
			throw new ExitException(400);
		}
		if (!Group::instance()->del($Request->route_ids[0])) {
			throw new ExitException(500);
		}
	}
	/**
	 * Get group's permissions
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_groups_permissions_get ($Request) {
		if (!isset($Request->route_ids[0])) {
			throw new ExitException(400);
		}
		Page::instance()->json(
			Group::instance()->get_permissions($Request->route_ids[0]) ?: []
		);
	}
	/**
	 * Update group's permissions
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_groups_permissions_put ($Request) {
		if (!isset($Request->route_ids[0], $_POST['permissions'])) {
			throw new ExitException(400);
		}
		if (!Group::instance()->set_permissions($_POST['permissions'], $Request->route_ids[0])) {
			throw new ExitException(500);
		}
	}
}
