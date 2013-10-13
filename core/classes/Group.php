<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *
 *  System/User/Group/add
 *  ['id'	=> <i>group_id</i>]
 *
 *  System/User/Group/del/before
 *  ['id'	=> <i>group_id</i>]
 *
 *  System/User/Group/del/after
 *  ['id'	=> <i>group_id</i>]
 *
 */
namespace	cs;
use			cs\Cache\Prefix,
			cs\DB\Accessor,
			cs\Permission\Any,
			h;
/**
 * Class for groups manipulating
 *
 * @method static \cs\Group instance($check = false)
 */
class Group extends Accessor {
	use	Singleton,
		Any;

	/**
	 * @var Prefix
	 */
	protected	$cache;

	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('System')->db('users');
	}
	protected function construct () {
		$this->cache	= new Prefix('groups');
	}
	/**
	 * Get group data
	 *
	 * @param int					$group
	 * @param bool|string			$item	If <b>false</b> - array will be returned, if title|description|data - corresponding item
	 *
	 * @return array|bool|mixed
	 */
	function get ($group, $item = false) {
		$group	= (int)$group;
		if (!$group) {
			return false;
		}
		$group_data = $this->cache->get($group, function () use ($group) {
			$group_data = $this->db()->qf(
				"SELECT
					`id`,
					`title`,
					`description`,
					`data`
				FROM `[prefix]groups`
				WHERE `id` = '$group'
				LIMIT 1"
			);
			$group_data['data'] = _json_decode($group_data['data']);
			return $group_data;
		});
		if ($item !== false) {
			if (isset($group_data[$item])) {
				return $group_data[$item];
			} else {
				return false;
			}
		} else {
			return $group_data;
		}
	}
	/**
	 * Add new group
	 *
	 * @param string $title
	 * @param string $description
	 *
	 * @return bool|int
	 */
	function add ($title, $description) {
		$title			= xap($title, false);
		$description	= xap($description, false);
		if (!$title || !$description) {
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
		)) {
			unset($this->cache->all);
			$id	= $this->db_prime()->id();
			Trigger::instance()->run(
				'System/User/Group/add',
				[
					'id'	=> $id
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
	 * @param array	$data	May contain items title|description|data
	 * @param int	$group
	 *
	 * @return bool
	 */
	function set ($data, $group) {
		$group = (int)$group;
		if (!$group) {
			return false;
		}
		$update = [];
		if (isset($data['title'])) {
			$update[] = '`title` = '.$this->db_prime()->s(xap($data['title'], false));
		}
		if (isset($data['description'])) {
			$update[] = '`description` = '.$this->db_prime()->s(xap($data['description'], false));
		}
		if (isset($data['data'])) {
			$update[] = '`data` = '.$this->db_prime()->s(_json_encode($data['data']));
		}
		$update	= implode(', ', $update);
		if (!empty($update) && $this->db_prime()->q("UPDATE `[prefix]groups` SET $update WHERE `id` = '$group' LIMIT 1")) {
			$Cache	= $this->cache;
			unset(
				$Cache->$group,
				$Cache->all
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Delete group
	 *
	 * @param int	$group
	 *
	 * @return bool
	 */
	function del ($group) {
		$group = (int)$group;
		Trigger::instance()->run(
			'System/User/Group/del/before',
			[
				'id'	=> $group
			]
		);
		if ($group != 1 && $group != 2 && $group != 3) {
			$return	= $this->db_prime()->q([
				"DELETE FROM `[prefix]groups` WHERE `id` = $group",
				"DELETE FROM `[prefix]users_groups` WHERE `group` = $group"
			]);
			$this->del_permissions_all($group);
			$Cache	= $this->cache;
			unset(
				Cache::instance()->{'users/groups'},
				$Cache->$group,
				$Cache->all
			);
			Trigger::instance()->run(
				'System/User/Group/del/after',
				[
					'id'	=> $group
				]
			);
			return (bool)$return;
		} else {
			return false;
		}
	}
	/**
	 * Get list of all groups
	 *
	 * @return array|bool		Every item in form of array('id' => <i>id</i>, 'title' => <i>title</i>, 'description' => <i>description</i>)
	 */
	function get_all () {
		return $this->cache->get('all', function () {
			return $this->db()->qfa(
				"SELECT
					`id`,
					`title`,
					`description`
				FROM `[prefix]groups`"
			);
		});
	}
	/**
	 * Get group permissions
	 *
	 * @param int		$group
	 *
	 * @return array
	 */
	function get_permissions ($group) {
		return $this->get_any_permissions($group, 'group');
	}
	/**
	 * Set group permissions
	 *
	 * @param array	$data
	 * @param int	$group
	 *
	 * @return bool
	 */
	function set_permissions ($data, $group) {
		return $this->set_any_permissions($data, (int)$group, 'group');
	}
	/**
	 * Delete all permissions of specified group
	 *
	 * @param int	$group
	 *
	 * @return bool
	 */
	function del_permissions_all ($group) {
		return $this->del_any_permissions_all((int)$group, 'group');
	}
}