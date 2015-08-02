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
	cs\Language,
	cs\Page,
	cs\User;
trait users {
	static function admin_users___get ($route_ids) {
		$User    = User::instance();
		$Page    = Page::instance();
		$columns = static::admin_users___search_options_get()['columns'];
		if (isset($route_ids[0])) {
			$result = static::admin_users___get_post_process(
				$User->get($columns, $route_ids[0])
			);
		} elseif (isset($_GET['ids'])) {
			$ids    = _int(explode(',', $_GET['ids']));
			$result = [];
			foreach ($ids as $id) {
				$result[] = static::admin_users___get_post_process(
					$User->get($columns, $id)
				);
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
	static protected function admin_users___get_post_process ($data) {
		$L                          = Language::instance();
		$data['reg_date_formatted'] = $data['reg_date'] ? date($L->_date, $data['reg_date']) : $L->undefined;
		$data['reg_ip_formatted']   = hex2ip($data['reg_ip'], 10);
		return $data;
	}
	static function admin_users___patch ($route_ids) {
		if (!isset($route_ids[0], $_POST['user'])) {
			error_code(400);
			return;
		}
		$User    = User::instance();
		$user_id = (int)$route_ids[0];
		$is_bot  = in_array(User::BOT_GROUP_ID, $User->get_groups($user_id));
		if ($is_bot && !@$_POST['user']['login'] && !@$_POST['user']['email']) {
			error_code(400);
			return;
		}
		$columns_allowed_to_edit = $is_bot
			? ['login', 'username', 'email', 'status']
			: ['login', 'username', 'email', 'language', 'timezone', 'status', 'block_until', 'avatar'];
		$user_data               = array_filter(
			$_POST['user'],
			function ($item) use ($columns_allowed_to_edit) {
				return in_array($item, $columns_allowed_to_edit, true);
			},
			ARRAY_FILTER_USE_KEY
		);
		foreach ($user_data as &$d) {
			$d = xap($d, false);
		}
		unset($d);
		if (!$user_data && ($is_bot || !isset($_POST['user']['password']))) {
			error_code(400);
			return;
		}
		$L    = Language::instance();
		$Page = Page::instance();
		if (
			isset($user_data['login']) &&
			$user_data['login'] !== $User->get('login', $user_id) &&
			$User->get_id(hash('sha224', $user_data['login']))
		) {
			error_code(400);
			$Page->error($L->login_occupied);
			return;
		}
		if (
			isset($user_data['email']) &&
			$user_data['email'] !== $User->get('email', $user_id) &&
			$User->get_id(hash('sha224', $user_data['email']))
		) {
			error_code(400);
			$Page->error($L->email_occupied);
			return;
		}
		if (!$User->set($user_data, null, $user_id)) {
			error_code(500);
			return;
		}
		if (!$is_bot && isset($_POST['user']['password']) && !$User->set_password($_POST['user']['password'], $user_id)) {
			error_code(500);
		}
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
	static function admin_users___search () {
		if (!isset($_POST['mode'], $_POST['column'], $_POST['text'], $_POST['page'], $_POST['limit'])) {
			error_code(400);
			return;
		}
		$mode           = $_POST['mode'];
		$column         = $_POST['column'];
		$text           = $_POST['text'];
		$page           = (int)$_POST['page'];
		$limit          = (int)$_POST['limit'];
		$search_options = static::admin_users___search_options_get();
		if (
			!in_array($mode, $search_options['modes']) ||
			(
				$column !== '' &&
				!in_array($column, $search_options['columns'])
			)
		) {
			error_code(400);
			return;
		}
		$cdb   = User::instance()->db();
		$where = static::admin_users___search_prepare_where($mode, $text, $column ?: $search_options['columns'], $cdb);
		/**
		 * Deleted users do not have any email, login or password, all of them are empty strings
		 */
		$where =
			"$where AND
			(
				`login`	!= `password_hash` OR
				`email`	!= `password_hash`
			)";
		$count = $cdb->qfs(
			[
				"SELECT COUNT(`id`)
				FROM `[prefix]users`
				WHERE $where"
			]
		);
		if (!$count) {
			error_code(404);
			return;
		}
		$ids = $cdb->qfas(
			[
				"SELECT `id`
				FROM `[prefix]users`
				WHERE $where
				ORDER BY `id`
				LIMIT %d, %d",
				($page - 1) * $limit,
				$limit
			]
		);
		Page::instance()->json(
			[
				'count' => $count,
				'users' => static::admin_users___search_get($ids, $search_options['columns'])
			]
		);
	}
	/**
	 * @param string           $mode
	 * @param string           $text
	 * @param string|string[]  $column
	 * @param \cs\DB\_Abstract $cdb
	 *
	 * @return string
	 */
	static protected function admin_users___search_prepare_where ($mode, $text, $column, $cdb) {
		$where = '1';
		if ($text && $mode) {
			switch ($mode) {
				case '=':
				case '!=':
				case '>':
				case '<':
				case '>=':
				case '<=':
				case 'LIKE':
				case 'NOT LIKE':
				case 'REGEXP':
				case 'NOT REGEXP':
					$where = static::admin_users___search_prepare_where_compose(
						"`%s` $mode %s",
						$column,
						$cdb->s($text)
					);
					break;
				case 'IN':
				case 'NOT IN':
					$where = static::admin_users___search_prepare_where_compose(
						"`%s` $mode (%s)",
						$column,
						implode(
							", ",
							$cdb->s(
								_trim(
									_trim(explode(',', $text), "'")
								)
							)
						)
					);
					break;
			}
		}
		return $where;
	}
	/**
	 * @param string          $where
	 * @param string|string[] $column
	 * @param string          $text
	 *
	 * @return string
	 */
	static protected function admin_users___search_prepare_where_compose ($where, $column, $text) {
		if (is_array($column)) {
			$return = [];
			foreach ($column as $c) {
				$return[] = sprintf($where, $c, $text);
			}
			return '('.implode(' OR ', $return).')';
		}
		return sprintf($where, $column, $text);
	}
	/**
	 * @param int[]    $users
	 * @param string[] $columns
	 *
	 * @return array[]
	 */
	static protected function admin_users___search_get ($users, $columns) {
		$User = User::instance();
		foreach ($users as &$user) {
			$groups         = (array)$User->get_groups($user);
			$user           =
				$User->get($columns, $user) +
				[
					'is_user'  => in_array(User::USER_GROUP_ID, $groups),
					'is_bot'   => in_array(User::BOT_GROUP_ID, $groups),
					'is_admin' => in_array(User::ADMIN_GROUP_ID, $groups),
					'username' => $User->username($user)
				];
			$user['reg_ip'] = hex2ip($user['reg_ip'], 10);
		}
		return $users;
	}
	static function admin_users___search_options () {
		Page::instance()->json(
			static::admin_users___search_options_get()
		);
	}
	/*
	 * @return string[][]
	 */
	static protected function admin_users___search_options_get () {
		return [
			'modes'   => [
				'=',
				'!=',
				'>',
				'<',
				'>=',
				'<=',
				'LIKE',
				'NOT LIKE',
				'IN',
				'NOT IN',
				'IS NULL',
				'IS NOT NULL',
				'REGEXP',
				'NOT REGEXP'
			],
			'columns' => array_values(
				array_filter(
					User::instance()->get_users_columns(),
					function ($column) {
						return $column !== 'password_hash';
					}
				)
			)
		];
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
