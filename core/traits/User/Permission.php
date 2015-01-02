<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\User;
use
	cs\Cache,
	cs\Group as System_Group,
	cs\Permission as System_Permission,
	cs\User;

/**
 * Trait that contains all methods from <i>>cs\User</i> for working with user permissions
 *
 * @property int				$id
 * @property \cs\Cache\Prefix	$cache
 */
trait Permission {
	/**
	 * Permissions cache for users
	 * @var array
	 */
	protected	$permissions	= [];
	/**
	 * Get permission state for specified user
	 *
	 * Rule: if not denied - allowed (users), if not allowed - denied (admins)
	 *
	 * @param string	$group	Permission group
	 * @param string	$label	Permission label
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool				If permission exists - returns its state for specified user, otherwise for admin permissions returns <b>false</b> and for
	 * 							others <b>true</b>
	 */
	function get_permission ($group, $label, $user = false) {
		$user			= (int)$user ?: $this->id;
		if ($this->system() || $user == User::ROOT_ID) {
			return true;
		}
		if (!$user) {
			return false;
		}
		if (!isset($this->permissions[$user])) {
			$this->permissions[$user]	= $this->cache->get("permissions/$user", function () use ($user) {
				$permissions	= [];
				if ($user != User::GUEST_ID) {
					$groups							= $this->get_groups($user);
					if (is_array($groups)) {
						$Group	= System_Group::instance();
						foreach ($groups as $group_id) {
							foreach ($Group->get_permissions($group_id) ?: [] as $p => $v) {
								$permissions[$p]	= $v;
							}
							unset($p, $v);
						}
					}
					unset($groups, $group_id);
				}
				foreach ($this->get_permissions($user) ?: [] as $p => $v) {
					$permissions[$p]	= $v;
				}
				return $permissions;
			});
		}
		$all_permission	= Cache::instance()->{'permissions/all'} ?: System_Permission::instance()->get_all();
		if (isset($all_permission[$group], $all_permission[$group][$label])) {
			$permission	= $all_permission[$group][$label];
			if (isset($this->permissions[$user][$permission])) {
				return (bool)$this->permissions[$user][$permission];
			} else {
				return $this->admin() ? true : strpos($group, 'admin/') !== 0;
			}
		} else {
			return true;
		}
	}
	/**
	 * Set permission state for specified user
	 *
	 * @param string	$group	Permission group
	 * @param string	$label	Permission label
	 * @param int		$value	1 - allow, 0 - deny, -1 - undefined (remove permission, and use default value)
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set_permission ($group, $label, $value, $user = false) {
		if ($permission = $this->get_permission(null, $group, $label)) {
			return $this->set_permissions(
				[
					$permission['id']	=> $value
				],
				$user
			);
		}
		return false;
	}
	/**
	 * Delete permission state for specified user
	 *
	 * @param string	$group	Permission group
	 * @param string	$label	Permission label
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_permission ($group, $label, $user = false) {
		return $this->set_permission($group, $label, -1, $user);
	}
	/**
	 * Get array of all permissions states for specified user
	 *
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return array|bool
	 */
	function get_permissions ($user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user) {
			return false;
		}
		return $this->get_any_permissions($user, 'user');
	}
	/**
	 * Set user's permissions according to the given array
	 *
	 * @param array		$data
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set_permissions ($data, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user) {
			return false;
		}
		return $this->set_any_permissions($data, $user, 'user');
	}
	/**
	 * Delete all user's permissions
	 *
	 * @param bool|int	$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_permissions_all ($user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user) {
			return false;
		}
		return $this->del_any_permissions_all($user, 'user');
	}
}
