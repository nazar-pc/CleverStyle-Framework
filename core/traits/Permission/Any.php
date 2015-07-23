<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Permission;
use
	cs\Cache;
/**
 * Class Any with common methods for User and Group classes
 *
 * @property Cache $cache
 *
 * @method \cs\DB\_Abstract db()
 * @method \cs\DB\_Abstract db_prime()
 */
trait Any {
	/**
	 * @param int    $id
	 * @param string $type
	 *
	 * @return int[]|false
	 */
	protected function get_any_permissions ($id, $type) {
		$id = (int)$id;
		if (!$id) {
			return false;
		}
		switch ($type) {
			case 'user':
				$table = '[prefix]users_permissions';
				break;
			case 'group':
				$table = '[prefix]groups_permissions';
				break;
			default:
				return false;
		}
		return $this->cache->get(
			"permissions/$id",
			function () use ($id, $table) {
				$permissions       = false;
				$permissions_array = $this->db()->qfa(
					"SELECT
						`permission`,
						`value`
					FROM `$table`
					WHERE `id` = '$id'"
				);
				if ($permissions_array) {
					$permissions = [];
					foreach ($permissions_array as $permission) {
						$permissions[$permission['permission']] = (int)(bool)$permission['value'];
					}
				}
				return $permissions;
			}
		);
	}
	/**
	 * @param array  $data
	 * @param int    $id
	 * @param string $type
	 *
	 * @return bool
	 */
	protected function set_any_permissions ($data, $id, $type) {
		$id = (int)$id;
		if (!is_array($data) || empty($data) || !$id) {
			return false;
		}
		switch ($type) {
			case 'user':
				$table = '[prefix]users_permissions';
				break;
			case 'group':
				$table = '[prefix]groups_permissions';
				break;
			default:
				return false;
		}
		$insert_update = [];
		$delete        = [];
		foreach ($data as $permission => $value) {
			if ($value == -1) {
				$delete[] = (int)$permission;
			} else {
				$insert_update[] = [$permission, (int)(bool)$value];
			}
		}
		unset($permission, $value);
		$return = true;
		if ($delete) {
			$delete = implode(', ', $delete);
			$return = (bool)$this->db_prime()->q(
				"DELETE FROM `$table`
				WHERE
					`id`			= '$id' AND
					`permission`	IN ($delete)"
			);
		}
		unset($delete);
		if ($insert_update) {
			$return =
				$return &&
				$this->db_prime()->insert(
					"INSERT INTO `$table`
						(
							`id`,
							`permission`,
							`value`
						) VALUES (
							'$id',
							'%d',
							'%d'
						)
					ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
					$insert_update
				);
		}
		unset(
			$insert_update,
			$this->cache->{"permissions/$id"}
		);
		if ($type == 'group') {
			unset(Cache::instance()->{'users/permissions'});
		}
		return (bool)$return;
	}
	/**
	 * @param int    $id
	 * @param string $type
	 *
	 * @return bool
	 */
	protected function del_any_permissions_all ($id, $type) {
		$id = (int)$id;
		if (!$id) {
			return false;
		}
		switch ($type) {
			case 'user':
				$table = '[prefix]users_permissions';
				break;
			case 'group':
				$table = '[prefix]groups_permissions';
				break;
			default:
				return false;
		}
		$return = $this->db_prime()->q(
			"DELETE FROM `$table`
			WHERE `id` = '$id'"
		);
		if ($return) {
			unset($this->cache->{"permissions/$id"});
			if ($type == 'group') {
				unset(Cache::instance()->{'users/permissions'});
			}
			return true;
		}
		return false;
	}
}
