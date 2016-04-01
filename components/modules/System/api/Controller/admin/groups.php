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
	cs\Response;

trait groups {
	/**
	 * Get array of groups data or data of specific group if id specified or data of several specified groups if specified in ids query parameter
	 *
	 * @param \cs\Request $Request
	 *
	 * @return array|array[]
	 *
	 * @throws ExitException
	 */
	static function admin_groups___get ($Request) {
		$Group = Group::instance();
		$id    = $Request->route_ids(0);
		$ids   = $Request->query('ids');
		if ($id) {
			$result = $Group->get($id);
		} elseif ($ids) {
			$result = $Group->get(explode(',', $ids));
		} else {
			$result = $Group->get($Group->get_all());
		}
		if (!$result) {
			throw new ExitException(404);
		}
		return $result;
	}
	/**
	 * Add new group
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_groups___post ($Request) {
		$data = $Request->data('title', 'description');
		if (!$data) {
			throw new ExitException(400);
		}
		if (Group::instance()->add(...array_values($data))) {
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
		$id   = $Request->route_ids(0);
		$data = $Request->data('title', 'description');
		if (!$id || !$data) {
			throw new ExitException(400);
		}
		if (!Group::instance()->set($id, ...array_values($data))) {
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
		$id = $Request->route_ids(0);
		if (!$id) {
			throw new ExitException(400);
		}
		if (!Group::instance()->del($id)) {
			throw new ExitException(500);
		}
	}
	/**
	 * Get group's permissions
	 *
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function admin_groups_permissions_get ($Request) {
		$id = $Request->route_ids(0);
		if (!$id) {
			throw new ExitException(400);
		}
		return Group::instance()->get_permissions($id) ?: [];
	}
	/**
	 * Update group's permissions
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_groups_permissions_put ($Request) {
		$id          = $Request->route_ids(0);
		$permissions = $Request->data('permissions');
		if (!$id || !$permissions) {
			throw new ExitException(400);
		}
		if (!Group::instance()->set_permissions($permissions, $id)) {
			throw new ExitException(500);
		}
	}
}
