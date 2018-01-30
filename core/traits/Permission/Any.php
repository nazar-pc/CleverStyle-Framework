<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
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
	 * @param string $type Either `user` or `group`
	 *
	 * @return int[]|false
	 */
	protected function get_any_permissions ($id, $type) {
		$id    = (int)$id;
		$table = '[prefix]users_permissions';
		if ($type == 'group') {
			$table = '[prefix]groups_permissions';
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
					WHERE `id` = '$id'
					ORDER BY `permission` ASC"
				);
				if (is_array($permissions_array)) {
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
	 * @param string $type Either `user` or `group`
	 *
	 * @return bool
	 */
	protected function set_any_permissions (array $data, $id, $type) {
		$id    = (int)$id;
		$table = '[prefix]users_permissions';
		if ($type == 'group') {
			$table = '[prefix]groups_permissions';
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
					"REPLACE INTO `$table`
						(
							`id`,
							`permission`,
							`value`
						) VALUES (
							'$id',
							'%d',
							'%d'
						)",
					$insert_update
				);
		}
		$this->cache->del("permissions/$id");
		if ($type == 'group') {
			Cache::instance()->del('users/permissions');
		}
		return (bool)$return;
	}
	/**
	 * @param int    $id
	 * @param string $type Either `user` or `group`
	 *
	 * @return bool
	 */
	protected function del_any_permissions_all ($id, $type) {
		$id    = (int)$id;
		$table = '[prefix]users_permissions';
		if ($type == 'group') {
			$table = '[prefix]groups_permissions';
		}
		$return = $this->db_prime()->q(
			"DELETE FROM `$table`
			WHERE `id` = '$id'"
		);
		$this->cache->del("permissions/$id");
		if ($type == 'group') {
			Cache::instance()->del('users/permissions');
		}
		return (bool)$return;
	}
}
