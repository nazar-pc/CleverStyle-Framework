<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
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
	cs\DB\Accessor,
	cs\Permission\Any;
/**
 * Class for groups manipulating
 *
 * @todo Remove data field from Groups DB table
 *
 * @method static Group instance($check = false)
 */
class Group {
	use
		Accessor,
		Singleton,
		Any;
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
	 * @param int|int[]    $group
	 * @param false|string $item If <b>false</b> - array will be returned, if title|description|data - corresponding item
	 *
	 * @return array|array[]|false|mixed
	 */
	function get ($group, $item = false) {
		if (is_array($group)) {
			foreach ($group as &$g) {
				$g = $this->get($g, $item);
			}
			return $group;
		}
		$group = (int)$group;
		if (!$group) {
			return false;
		}
		$group_data = $this->cache->get(
			$group,
			function () use ($group) {
				$group_data = $this->db()->qf(
					"SELECT
						`id`,
						`title`,
						`description`
					FROM `[prefix]groups`
					WHERE `id` = '$group'
					LIMIT 1"
				);
				return $group_data;
			}
		);
		if ($item === false) {
			return $group_data;
		}
		if (isset($group_data[$item])) {
			return $group_data[$item];
		}
		return false;
	}
	/**
	 * Get list of all groups
	 *
	 * @return array Every item in form of ['id' => <i>id</i>, 'title' => <i>title</i>, 'description' => <i>description</i>]
	 */
	function get_all () {
		return $this->cache->get(
			'all',
			function () {
				return $this->db()->qfa(
					"SELECT
						`id`,
						`title`,
						`description`
					FROM `[prefix]groups`"
				);
			}
		) ?: [];
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
		$title       = xap($title, false);
		$description = xap($description, false);
		if (!$title) {
			return false;
		}
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]groups`
				(
					`title`,
					`description`
				) VALUES (
					'%s',
					'%s'
				)",
			$title,
			$description
		)
		) {
			unset($this->cache->all);
			$id = $this->db_prime()->id();
			Event::instance()->fire(
				'System/User/Group/add',
				[
					'id' => $id
				]
			);
			return $id;
		} else {
			return false;
		}
	}
	/**
	 * Set group data
	 *
	 * @param int    $group
	 * @param string $title
	 * @param string $description
	 *
	 * @return bool
	 */
	function set ($group, $title, $description) {
		$group = (int)$group;
		if (!$group) {
			return false;
		}
		$result = $this->db_prime()->q(
			"UPDATE `[prefix]groups`
			SET
				`title`			= '%s',
				`description`	= '%s'
			WHERE `id` = '%d'
			LIMIT 1",
			xap($title, false),
			xap($description, false),
			$group
		);
		if ($result) {
			$Cache = $this->cache;
			unset(
				$Cache->$group,
				$Cache->all
			);
		}
		return (bool)$result;
	}
	/**
	 * Delete group
	 *
	 * @param int|int[] $group
	 *
	 * @return bool
	 */
	function del ($group) {
		if (is_array($group)) {
			foreach ($group as &$g) {
				$g = (int)$this->del($g);
			}
			return (bool)array_product($group);
		}
		$group = (int)$group;
		if (in_array($group, [User::ADMIN_GROUP_ID, User::USER_GROUP_ID, User::BOT_GROUP_ID])) {
			return false;
		}
		Event::instance()->fire(
			'System/User/Group/del/before',
			[
				'id' => $group
			]
		);
		$result = $this->db_prime()->q(
			[
				"DELETE FROM `[prefix]groups` WHERE `id` = $group",
				"DELETE FROM `[prefix]users_groups` WHERE `group` = $group"
			]
		);
		if ($result) {
			$this->del_permissions_all($group);
			$Cache = $this->cache;
			unset(
				Cache::instance()->{'users/groups'},
				$Cache->$group,
				$Cache->all
			);
			Event::instance()->fire(
				'System/User/Group/del/after',
				[
					'id' => $group
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
