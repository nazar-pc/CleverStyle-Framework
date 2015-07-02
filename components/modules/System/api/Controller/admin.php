<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller;
use
	cs\Cache,
	cs\DB,
	cs\Group,
	cs\Mail,
	cs\Page,
	cs\Permission,
	cs\Route,
	cs\Storage,
	cs\User;
trait admin {
	static function admin_cache_delete () {
		$Cache = Cache::instance();
		$Page  = Page::instance();
		$rc    = Route::instance()->route;
		if (isset($rc[2])) {
			switch ($rc[2]) {
				case 'clean_cache':
					time_limit_pause();
					if ($_POST['partial_path']) {
						$result = $Cache->del($_POST['partial_path']);
					} else {
						$result = $Cache->clean();
						clean_classes_cache();
					}
					time_limit_pause(false);
					if ($result) {
						$Cache->disable();
						$Page->content(1);
					} else {
						$Page->content(0);
					}
					break;
				case 'clean_pcache':
					if (clean_pcache()) {
						$Page->content(1);
					} else {
						$Page->content(0);
					}
					break;
			}
		} else {
			$Page->content(0);
		}
	}
	static function admin_databases_test_get () {
		$db = DB::instance();
		if (isset($_GET['mirror_index'])) {
			$result = $db->test([$_GET['index'], $_GET['mirror_index']]);
		} elseif (isset($_GET['index'])) {
			$result = $db->test([$_GET['index']]);
		} else {
			$result = $db->test($_GET['db']);
		}
		Page::instance()->json(
			(int)$result
		);
	}
	static function admin_email_sending_test_get () {
		if (!isset($_GET['email'])) {
			error_code(400);
			return;
		}
		if (!Mail::instance()->send_to($_GET['email'], 'Email testing on '.get_core_ml_text('name'), 'Test email')) {
			error_code(500);
		}
	}
	static function admin_groups_get ($route_ids) {
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
	static function admin_storages_test_get () {
		$Storage = Storage::instance();
		if (isset($_GET['index'])) {
			$result = $Storage->test([$_GET['index']]);
		} else {
			$result = $Storage->test($_GET['storage']);
		}
		Page::instance()->json(
			(int)$result
		);
	}
	static function admin_users_get ($route_ids) {
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
}
