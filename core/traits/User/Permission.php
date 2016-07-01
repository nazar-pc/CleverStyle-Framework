<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\User;
use
	cs\Cache,
	cs\Group as System_Group,
	cs\Permission as System_Permission,
	cs\Permission\Any,
	cs\User;

/**
 * Trait that contains all methods from <i>>cs\User</i> for working with user permissions
 *
 * @property int              $id
 * @property \cs\Cache\Prefix $cache
 *
 * @method false|int[]        get_groups(false|int $user)
 * @method bool               admin()
 */
trait Permission {
	use
		Any;
	/**
	 * Permissions cache for users
	 * @var array
	 */
	protected $permissions = [];
	/**
	 * Get permission state for specified user
	 *
	 * Rule: if not denied - allowed (users), if not allowed - denied (admins)
	 *
	 * @param string    $group Permission group
	 * @param string    $label Permission label
	 * @param false|int $user  If not specified - current user assumed
	 *
	 * @return bool If permission exists - returns its state for specified user, otherwise for admin permissions returns <b>false</b> and for others <b>true</b>
	 */
	function get_permission ($group, $label, $user = false) {
		$user = (int)$user ?: $this->id;
		if ($user == User::ROOT_ID) {
			return true;
		}
		$group_label_exploded = explode('/', "$group/$label");
		/**
		 * Default permissions values:
		 *
		 * - only administrators have access to `admin/*` URLs by default
		 * - only administrators have access to `api/{module}/admin/*` URLs by default
		 * - all other URLs are available to everyone by default
		 */
		$admin_section = $group_label_exploded[0] === 'admin' || ($group_label_exploded[0] === 'api' && @$group_label_exploded[2] === 'admin');
		if (!$user || ($admin_section && !$this->admin())) {
			return false;
		}
		$all_permissions = Cache::instance()->get('permissions/all');
		if ($all_permissions === false) {
			$all_permissions = System_Permission::instance()->get_all();
		}
		if (isset($all_permissions[$group][$label])) {
			$user_permissions = $this->get_permission_internal($user);
			$permission_id    = $all_permissions[$group][$label];
			return isset($user_permissions[$permission_id]) ? (bool)$user_permissions[$permission_id] : !$admin_section;
		}
		return true;
	}
	/**
	 * @param int $user
	 *
	 * @return array
	 */
	protected function get_permission_internal ($user) {
		if (isset($this->permissions[$user])) {
			return $this->permissions[$user];
		}
		$permissions = $this->cache->get(
			"permissions/computed/$user",
			function () use ($user) {
				$permissions = [];
				if ($user != User::GUEST_ID) {
					$Group = System_Group::instance();
					foreach ($this->get_groups($user) ?: [] as $group_id) {
						$permissions = ($Group->get_permissions($group_id) ?: []) + $permissions;
					}
				}
				$permissions = ($this->get_permissions($user) ?: []) + $permissions;
				return $permissions;
			}
		);
		if ($this->memory_cache || $user == User::GUEST_ID) {
			$this->permissions[$user] = $permissions;
		}
		return $permissions;
	}
	/**
	 * Set permission state for specified user
	 *
	 * @param string    $group Permission group
	 * @param string    $label Permission label
	 * @param int       $value 1 - allow, 0 - deny, -1 - undefined (remove permission, and use default value)
	 * @param false|int $user  If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set_permission ($group, $label, $value, $user = false) {
		$permission = System_Permission::instance()->get(null, $group, $label);
		if ($permission) {
			return $this->set_permissions(
				[
					$permission[0]['id'] => $value
				],
				$user
			);
		}
		return false;
	}
	/**
	 * Delete permission state for specified user
	 *
	 * @param string    $group Permission group
	 * @param string    $label Permission label
	 * @param false|int $user  If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_permission ($group, $label, $user = false) {
		return $this->set_permission($group, $label, -1, $user);
	}
	/**
	 * Get array of all permissions states for specified user
	 *
	 * @param false|int $user If not specified - current user assumed
	 *
	 * @return int[]|false
	 */
	function get_permissions ($user = false) {
		$user = (int)$user ?: $this->id;
		if ($user == User::ROOT_ID || !$user) {
			return false;
		}
		return $this->get_any_permissions($user, 'user');
	}
	/**
	 * Set user's permissions according to the given array
	 *
	 * @param array     $data
	 * @param false|int $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set_permissions ($data, $user = false) {
		$user = (int)$user ?: $this->id;
		if ($user == User::ROOT_ID || !$user) {
			return false;
		}
		$result = $this->set_any_permissions($data, $user, 'user');
		$this->cache->del("permissions/computed/$user");
		unset($this->permissions[$user]);
		return $result;
	}
	/**
	 * Delete all user's permissions
	 *
	 * @param false|int $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_permissions_all ($user = false) {
		$user = (int)$user ?: $this->id;
		if ($user == User::ROOT_ID || !$user) {
			return false;
		}
		$result = $this->del_any_permissions_all($user, 'user');
		$this->cache->del("permissions/computed/$user");
		unset($this->permissions[$user]);
		return $result;
	}
}
