<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Group,
	cs\Page,
	cs\Permission,
	cs\User;
trait permissions {
	static function admin_permissions___get ($route_ids) {
		$Permission = Permission::instance();
		if (isset($route_ids[0])) {
			$result = $Permission->get($route_ids[0]);
			if (!$result) {
				error_code(404);
				return;
			}
		} else {
			$result = $Permission->get_all();
		}
		Page::instance()->json($result);
	}
	static function admin_permissions___post () {
		if (!isset($_POST['group'], $_POST['label'])) {
			error_code(400);
			return;
		}
		if (Permission::instance()->add($_POST['group'], $_POST['label'])) {
			status_code(201);
		} else {
			error_code(500);
		}
	}
	static function admin_permissions___put ($route_ids) {
		if (!isset($route_ids[0], $_POST['group'], $_POST['label'])) {
			error_code(400);
			return;
		}
		if (!Permission::instance()->set($route_ids[0], $_POST['group'], $_POST['label'])) {
			error_code(500);
		}
	}
	static function admin_permissions___delete ($route_ids) {
		if (!isset($route_ids[0])) {
			error_code(400);
			return;
		}
		if (!Permission::instance()->del($route_ids[0])) {
			error_code(500);
		}
	}
	static function admin_permissions_for_item_get () {
		if (!isset($_GET['group'], $_GET['label'])) {
			error_code(400);
			return;
		}
		$User       = User::instance();
		$Permission = Permission::instance();
		$permission = $Permission->get(null, $_GET['group'], $_GET['label']);
		$data       = [
			'groups' => [],
			'users'  => []
		];
		if (isset($permission)) {
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
	static function admin_permissions_for_item_post () {
		if (!isset($_POST['group'], $_POST['label'])) {
			error_code(400);
			return;
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
			error_code(500);
			return;
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
			error_code(500);
		}
	}
}
