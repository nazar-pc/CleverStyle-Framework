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
	cs\Page,
	cs\User;
trait users {
	static function admin_users___get ($route_ids) {
		$User    = User::instance();
		$Page    = Page::instance();
		$columns = array_filter(
			$User->get_users_columns(),
			function ($column) {
				return $column !== 'password_hash';
			}
		);
		if (isset($route_ids[0])) {
			$result = $User->get($columns, $route_ids[0]);
		} elseif (isset($_GET['ids'])) {
			$ids    = _int(explode(',', $_GET['ids']));
			$result = [];
			foreach ($ids as $id) {
				$result[] = $User->get($columns, $id);
			}
		} elseif (isset($_GET['search'])) {
			$result = _int($User->search_users($_GET['search']));
		} else {
			error_code(400);
			return;
		}
		if (!$result) {
			error_code(404);
			return;
		}
		$Page->json($result);
	}
	static function admin_users___post () {
		if (!isset($_POST['type'])) {
			error_code(400);
			return;
		}
		$User = User::instance();
		$Page = Page::instance();
		if ($_POST['type'] === 'user' && isset($_POST['email'])) {
			$result = $User->registration($_POST['email'], false, false);
			if (!$result) {
				error_code(500);
				return;
			}
			status_code(201);
			$Page->json(
				[
					'login'    => $User->get('login', $result['id']),
					'password' => $result['password']
				]
			);
		} elseif ($_POST['type'] === 'bot' && isset($_POST['name'], $_POST['user_agent'], $_POST['ip'])) {
			if ($User->add_bot($_POST['name'], $_POST['user_agent'], $_POST['ip'])) {
				status_code(201);
			} else {
				error_code(500);
			}
		} else {
			error_code(400);
		}
	}
	static function admin_users_permissions_get ($route_ids) {
		if (!isset($route_ids[0])) {
			error_code(400);
			return;
		}
		Page::instance()->json(
			User::instance()->get_permissions($route_ids[0]) ?: []
		);
	}
	static function admin_users_permissions_post ($route_ids) {
		if (!isset($route_ids[0], $_POST['permissions'])) {
			error_code(400);
			return;
		}
		if (!User::instance()->set_permissions($_POST['permissions'], $route_ids[0])) {
			error_code(500);
		}
	}
}
