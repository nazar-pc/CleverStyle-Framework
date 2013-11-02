<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			cs\Cache\Prefix,
			cs\DB\Accessor,
			h;
/**
 * Class for permissions manipulating
 *
 * @method static \cs\Permission instance($check = false)
 */
class Permission {
	use	Accessor,
		Singleton;

	protected	$permissions_table	= [];		//Array of all permissions for quick selecting

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
		$this->cache	= new Prefix('permissions');
	}
	/**
	 * Get permission data<br>
	 * If <b>$group</b> or/and <b>$label</b> parameter is specified, <b>$id</b> is ignored.
	 *
	 * @param int|null		$id
	 * @param null|string	$group
	 * @param null|string	$label
	 * @param string		$condition	and|or
	 *
	 * @return array|bool			If only <b>$id</b> specified - result is array of permission data,
	 * 								in other cases result will be array of arrays of corresponding permissions data.
	 */
	function get ($id = null, $group = null, $label = null, $condition = 'and') {
		switch ($condition) {
			case 'or':
				$condition = 'OR';
			break;
			default:
				$condition = 'AND';
			break;
		}
		if ($group !== null && $group && $label !== null && $label) {
			return $this->db()->qfa([
				"SELECT
					`id`,
					`label`,
					`group`
				FROM `[prefix]permissions`
				WHERE
					`group` = '%s' $condition
					`label` = '%s'",
				$group,
				$label
			]);
		} elseif ($group !== null && $group) {
			return $this->db()->qfa([
				"SELECT
					`id`,
					`label`,
					`group`
				FROM `[prefix]permissions`
				WHERE `group` = '%s'",
				$group
			]);
		} elseif ($label !== null && $label) {
			return $this->db()->qfa([
				"SELECT
					`id`,
					`label`,
					`group`
				FROM `[prefix]permissions`
				WHERE `label` = '%s'",
				$label
			]);
		} else {
			$id		= (int)$id;
			if (!$id) {
				return false;
			}
			return $this->db()->qf(
				"SELECT
					`id`,
					`label`,
					`group`
				FROM `[prefix]permissions`
				WHERE `id` = '$id'
				LIMIT 1"
			);
		}
	}
	/**
	 * Add permission
	 *
	 * @param string	$group
	 * @param string	$label
	 *
	 * @return bool|int			Group id or <b>false</b> on failure
	 */
	function add ($group, $label) {
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]permissions`
				(
					`label`,
					`group`
				) VALUES (
					'%s',
					'%s'
				)",
			xap($label),
			xap($group)
		)) {
			$this->del_all_cache();
			return $this->db_prime()->id();
		}
		return false;
	}
	/**
	 * Set permission
	 *
	 * @param int		$id
	 * @param string	$group
	 * @param string	$label
	 *
	 * @return bool
	 */
	function set ($id, $group, $label) {
		$id		= (int)$id;
		if (!$id) {
			return false;
		}
		if ($this->db_prime()->q(
			"UPDATE `[prefix]permissions`
			SET
				`label` = '%s',
				`group` = '%s'
			WHERE `id` = '$id'
			LIMIT 1",
			xap($label),
			xap($group)
		)) {
			$this->del_all_cache();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Deletion of permission or array of permissions
	 *
	 * @param int|int[]	$id
	 *
	 * @return bool
	 */
	function del ($id) {
		if (is_array($id) && !empty($id)) {
			foreach ($id as &$item) {
				$item = (int)$item;
			}
			$id = implode(',', $id);
			return $this->db_prime()->q([
				"DELETE FROM `[prefix]permissions` WHERE `id` IN ($id)",
				"DELETE FROM `[prefix]users_permissions` WHERE `permission` IN ($id)",
				"DELETE FROM `[prefix]groups_permissions` WHERE `permission` IN ($id)"
			]);
		}
		$id		= (int)$id;
		if (!$id) {
			return false;
		}
		if ($this->db_prime()->q([
			"DELETE FROM `[prefix]permissions` WHERE `id` = '$id' LIMIT 1",
			"DELETE FROM `[prefix]users_permissions` WHERE `permission` = '$id'",
			"DELETE FROM `[prefix]groups_permissions` WHERE `permission` = '$id'"
		])) {
			$Cache	= $this->cache;
			unset(
				$Cache->users,
				$Cache->groups
			);
			$this->del_all_cache();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Returns array of all permissions grouped by permissions groups
	 *
	 * @return array	Format of array: ['group']['label'] = <i>permission_id</i>
	 */
	function get_all () {
		if (empty($this->permissions_table)) {
			$this->permissions_table = $this->cache->get('all', function () {
				$all_permissions	= [];
				$data				= $this->db()->qfa(
					'SELECT
						`id`,
						`label`,
						`group`
					FROM `[prefix]permissions`'
				);
				foreach ($data as $item) {
					if (!isset($all_permissions[$item['group']])) {
						$all_permissions[$item['group']] = [];
					}
					$all_permissions[$item['group']][$item['label']] = $item['id'];
				}
				unset($data, $item);
				return $all_permissions;
			});
		}
		return $this->permissions_table;
	}
	/**
	 * Deletion of permission table (is used after adding, setting or deletion of permission)
	 */
	protected function del_all_cache () {
		$this->permissions_table = [];
		unset($this->cache->all);
	}
}