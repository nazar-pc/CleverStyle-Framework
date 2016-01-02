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
	cs\Permission,
	cs\User;
trait permissions {
	/**
	 * Get array of permissions data or data of specific permission if id specified
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_permissions___get ($route_ids) {
		$Permission = Permission::instance();
		if (isset($route_ids[0])) {
			$result = $Permission->get($route_ids[0]);
			if (!$result) {
				throw new ExitException(404);
			}
		} else {
			$result = $Permission->get_all();
		}
		Page::instance()->json($result);
	}
	/**
	 * Add new permission
	 *
	 * @throws ExitException
	 */
	static function admin_permissions___post () {
		if (!isset($_POST['group'], $_POST['label'])) {
			throw new ExitException(400);
		}
		if (Permission::instance()->add($_POST['group'], $_POST['label'])) {
			status_code(201);
		} else {
			throw new ExitException(500);
		}
	}
	/**
	 * Update permission's data
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_permissions___put ($route_ids) {
		if (!isset($route_ids[0], $_POST['group'], $_POST['label'])) {
			throw new ExitException(400);
		}
		if (!Permission::instance()->set($route_ids[0], $_POST['group'], $_POST['label'])) {
			throw new ExitException(500);
		}
	}
	/**
	 * Delete permission
	 *
	 * @param int[] $route_ids
	 *
	 * @throws ExitException
	 */
	static function admin_permissions___delete ($route_ids) {
		if (!isset($route_ids[0])) {
			throw new ExitException(400);
		}
		if (!Permission::instance()->del($route_ids[0])) {
			throw new ExitException(500);
		}
	}
	/**
	 * Get permissions for specific item
	 *
	 * @throws ExitException
	 */
	static function admin_permissions_for_item_get () {
		if (!isset($_GET['group'], $_GET['label'])) {
			throw new ExitException(400);
		}
		$User       = User::instance();
		$Permission = Permission::instance();
		$permission = $Permission->get(null, $_GET['group'], $_GET['label']);
		$data       = [
			'groups' => [],
			'users'  => []
		];
		if ($permission) {
			$data['groups'] = array_column(
				$User->db()->qfa(
					[
						"SELECT
							`id`,
							`value`
						FROM `[prefix]groups_permissions`
						WHERE
							`permission`	= '%s'",
						$permission[0]['id']
					]
				) ?: [],
				'value',
				'id'
			);
			$data['users']  = array_column(
				$User->db()->qfa(
					[
						"SELECT
							`id`,
							`value`
						FROM `[prefix]users_permissions`
						WHERE
							`permission`	= '%s'",
						$permission[0]['id']
					]
				) ?: [],
				'value',
				'id'
			);
		}
		Page::instance()->json(
			[
				'groups' => (object)$data['groups'],
				'users'  => (object)$data['users']
			]
		);
	}
	/**
	 * Get permissions for specific item
	 *
	 * @throws ExitException
	 */
	static function admin_permissions_for_item_post () {
		if (!isset($_POST['group'], $_POST['label'])) {
			throw new ExitException(400);
		}
		$Group      = Group::instance();
		$Permission = Permission::instance();
		$User       = User::instance();
		$permission = $Permission->get(null, $_POST['group'], $_POST['label']);
		// We'll create permission if needed
		$permission = $permission
			? $permission[0]['id']
			: $Permission->add($_POST['group'], $_POST['label']);
		if (!$permission) {
			throw new ExitException(500);
		}
		$result = true;
		if (isset($_POST['groups'])) {
			foreach ($_POST['groups'] as $group => $value) {
				$result = $result && $Group->set_permissions([$permission => $value], $group);
			}
		}
		if (isset($_POST['users'])) {
			foreach ($_POST['users'] as $user => $value) {
				$result = $result && $User->set_permissions([$permission => $value], $user);
			}
		}
		if (!$result) {
			throw new ExitException(500);
		}
	}
}
