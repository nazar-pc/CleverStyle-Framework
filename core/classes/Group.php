<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Provides next events:<br>
 *
 *  System/User/Group/add
 *  ['id' => <i>group_id</i>]
 *
 *  System/User/Group/del/before
 *  ['id' => <i>group_id</i>]
 *
 *  System/User/Group/del/after
 *  ['id' => <i>group_id</i>]
 *
 */
namespace cs;
use
	cs\Cache\Prefix,
	cs\Permission\Any;
/**
 * Class for groups manipulating
 *
 * @method static Group instance($check = false)
 */
class Group {
	use
		CRUD_helpers,
		Singleton,
		Any;
	protected $data_model = [
		'id'          => 'int:0',
		'title'       => 'html',
		'description' => 'html'
	];
	protected $table      = '[prefix]groups';
	/**
	 * @var Prefix
	 */
	protected $cache;
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('System')->db('users');
	}
	protected function construct () {
		$this->cache = new Prefix('groups');
	}
	/**
	 * Get group data
	 *
	 * @param int|int[] $id
	 *
	 * @return array|array[]|false
	 */
	function get ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->get($i);
			}
			return $id;
		}
		$id = (int)$id;
		if (!$id) {
			return false;
		}
		return $this->cache->get(
			$id,
			function () use ($id) {
				return $this->read($id);
			}
		);
	}
	/**
	 * Get array of all groups
	 *
	 * @return array
	 */
	function get_all () {
		return $this->cache->get(
			'all',
			function () {
				return $this->db()->qfas(
					"SELECT `id`
					FROM `$this->table`"
				);
			}
		);
	}
	/**
	 * Add new group
	 *
	 * @param string $title
	 * @param string $description
	 *
	 * @return false|int
	 */
	function add ($title, $description) {
		$id = $this->create(
			[
				$title,
				$description
			]
		);
		if ($id) {
			unset($this->cache->all);
			Event::instance()->fire(
				'System/User/Group/add',
				[
					'id' => $id
				]
			);
		}
		return $id;
	}
	/**
	 * Set group data
	 *
	 * @param int    $id
	 * @param string $title
	 * @param string $description
	 *
	 * @return bool
	 */
	function set ($id, $title, $description) {
		$id     = (int)$id;
		$result = $this->update(
			[
				$id,
				$title,
				$description
			]
		);
		if ($result) {
			$Cache = $this->cache;
			unset(
				$Cache->$id,
				$Cache->all
			);
		}
		return (bool)$result;
	}
	/**
	 * Delete group
	 *
	 * @param int|int[] $id
	 *
	 * @return bool
	 */
	function del ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = (int)$this->del($i);
			}
			return (bool)array_product($id);
		}
		$id = (int)$id;
		if (in_array($id, [User::ADMIN_GROUP_ID, User::USER_GROUP_ID, User::BOT_GROUP_ID])) {
			return false;
		}
		Event::instance()->fire(
			'System/User/Group/del/before',
			[
				'id' => $id
			]
		);
		$result = $this->db_prime()->q(
			[
				"DELETE FROM `[prefix]groups` WHERE `id` = $id",
				"DELETE FROM `[prefix]users_groups` WHERE `group` = $id"
			]
		);
		if ($result) {
			$this->del_permissions_all($id);
			$Cache = $this->cache;
			unset(
				Cache::instance()->{'users/groups'},
				$Cache->$id,
				$Cache->all
			);
			Event::instance()->fire(
				'System/User/Group/del/after',
				[
					'id' => $id
				]
			);
		}
		return (bool)$result;
	}
	/**
	 * Get group permissions
	 *
	 * @param int $group
	 *
	 * @return int[]|false
	 */
	function get_permissions ($group) {
		return $this->get_any_permissions($group, 'group');
	}
	/**
	 * Set group permissions
	 *
	 * @param array $data
	 * @param int   $group
	 *
	 * @return bool
	 */
	function set_permissions ($data, $group) {
		return $this->set_any_permissions($data, (int)$group, 'group');
	}
	/**
	 * Delete all permissions of specified group
	 *
	 * @param int $group
	 *
	 * @return bool
	 */
	function del_permissions_all ($group) {
		return $this->del_any_permissions_all((int)$group, 'group');
	}
}
