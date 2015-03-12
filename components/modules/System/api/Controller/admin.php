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
	cs\Config,
	cs\DB,
	cs\Language,
	cs\Mail,
	cs\Page,
	cs\Route,
	cs\Storage,
	cs\User,
	h;
trait admin {
	static function admin () {
		if (!User::instance()->admin()) {
			error_code(403);
		}
	}
	static function admin_blocks_search_users_get () {
		$L           = Language::instance();
		$Page        = Page::instance();
		$User        = User::instance();
		$users_list  = $User->search_users($_GET['search_phrase']);
		$found_users = explode(',', $_GET['found_users']);
		$permission  = (int)$_GET['permission'];
		$content     = [];
		foreach ($users_list as $user) {
			if (in_array($user, $found_users)) {
				continue;
			}
			$found_users[] = $user;
			$value         = $User->db()->qfs(
				[
					"SELECT `value`
		FROM `[prefix]users_permissions`
		WHERE
			`id`			= '%s' AND
			`permission`	= '%s'",
					$user,
					$permission
				]
			);
			$content[]     = [
				$User->username($user),
				h::radio(
					[
						'name'    => 'users['.$user.']',
						'checked' => $value !== false ? $value : -1,
						'value'   => [-1, 0, 1],
						'in'      => [
							$L->inherited.' ('.($value !== false && !$value ? '-' : '+').')',
							$L->deny,
							$L->allow
						]
					]
				)
			];
		}
		$Page->json(
			h::{'cs-table[right-left] cs-table-row| cs-table-cell'}($content)
		);
	}
	static function admin_cache_delete () {
		$Cache  = Cache::instance();
		$Page   = Page::instance();
		$rc     = Route::instance()->route;
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
		if (!Mail::instance()->send_to($_GET['email'], 'Email testing on '.Config::instance()->core['name'], 'Test email')) {
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
}
