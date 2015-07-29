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
	cs\Page;
trait groups {
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
			$result = $Group->get_all();
		}
		if (!$result) {
			error_code(404);
			return;
		}
		$Page->json($result);
	}
	static function admin_groups___post () {
		if (!isset($_POST['title'], $_POST['description'])) {
			error_code(400);
			return;
		}
		if (Group::instance()->add($_POST['title'], $_POST['description'])) {
			status_code(201);
		} else {
			error_code(500);
		}
	}
	static function admin_groups___put ($route_ids) {
		if (!isset($route_ids[0], $_POST['title'], $_POST['description'])) {
			error_code(400);
			return;
		}
		if (!Group::instance()->set($route_ids[0], $_POST['title'], $_POST['description'])) {
			error_code(500);
		}
	}
	static function admin_groups___delete ($route_ids) {
		if (!isset($route_ids[0])) {
			error_code(400);
			return;
		}
		if (!Group::instance()->del($route_ids[0])) {
			error_code(500);
		}
	}
	static function admin_groups_permissions_get ($route_ids) {
		if (!isset($route_ids[0])) {
			error_code(400);
			return;
		}
		Page::instance()->json(
			Group::instance()->get_permissions($route_ids[0]) ?: []
		);
	}
	static function admin_groups_permissions_post ($route_ids) {
		if (!isset($route_ids[0], $_POST['permissions'])) {
			error_code(400);
			return;
		}
		if (!Group::instance()->set_permissions($_POST['permissions'], $route_ids[0])) {
			error_code(500);
		}
	}
}
