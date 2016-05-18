<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

/**
 * Class for permissions manipulating
 *
 * @method static $this instance($check = false)
 */
class Permission {
	use
		CRUD_helpers,
		Singleton;

	protected $data_model = [
		'id'    => 'int:0',
		'group' => 'text',
		'label' => 'text'
	];
	protected $table      = '[prefix]permissions';
	/**
	 * Array of all permissions for quick selecting
	 * @var array
	 */
	protected $permissions_table = [];
	/**
	 * @var Cache\Prefix
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
		$this->cache = Cache::prefix('permissions');
	}
	/**
	 * Get permission data<br>
	 * If <b>$group</b> or/and <b>$label</b> parameter is specified, <b>$id</b> is ignored.
	 *
	 * @param int|null    $id
	 * @param null|string $group
	 * @param null|string $label
	 *
	 * @return array|false If only <b>$id</b> specified - result is array of permission data, in other cases result will be array of arrays of corresponding
	 *                     permissions data
	 */
	function get ($id = null, $group = null, $label = null) {
		if ($group !== null || $label !== null) {
			return $this->read(
				$this->search(
					[
						'group' => $group,
						'label' => $label
					],
					1,
					PHP_INT_MAX,
					'id',
					true
				) ?: []
			);
		} else {
			return $this->read($id);
		}
	}
	/**
	 * Add permission
	 *
	 * @param string $group
	 * @param string $label
	 *
	 * @return false|int Group id or <b>false</b> on failure
	 */
	function add ($group, $label) {
		$id = $this->create($group, $label);
		if ($id) {
			$this->del_all_cache();
		}
		return $id;
	}
	/**
	 * Set permission
	 *
	 * @param int    $id
	 * @param string $group
	 * @param string $label
	 *
	 * @return bool
	 */
	function set ($id, $group, $label) {
		$result = $this->update($id, $group, $label);
		if ($result) {
			$this->del_all_cache();
		}
		return $result;
	}
	/**
	 * Deletion of permission or array of permissions
	 *
	 * @param int|int[] $id
	 *
	 * @return bool
	 */
	function del ($id) {
		if (!$id) {
			return false;
		}
		$id = implode(',', (array)_int($id));
		if ($this->db_prime()->q(
			[
				"DELETE FROM `[prefix]permissions`
				WHERE `id` IN ($id)",
				"DELETE FROM `[prefix]users_permissions`
				WHERE `permission` IN ($id)",
				"DELETE FROM `[prefix]groups_permissions`
				WHERE `permission` IN ($id)"
			]
		)
		) {
			$Cache = $this->cache;
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
	 * @return array Format of array: ['group']['label'] = <i>permission_id</i>
	 */
	function get_all () {
		if (empty($this->permissions_table)) {
			$this->permissions_table = $this->cache->get(
				'all',
				function () {
					$data            = $this->read(
						$this->search([], 1, PHP_INT_MAX, 'id', true) ?: []
					);
					$all_permissions = [];
					foreach ($data as $item) {
						$all_permissions[$item['group']][$item['label']] = $item['id'];
					}
					return $all_permissions;
				}
			);
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
