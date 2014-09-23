<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\User;
use cs\User;

/**
 * Trait that contains all methods from <i>>cs\User</i> for working with user groups
 *
 * @property int				$id
 * @property \cs\Cache\Prefix	$cache
 */
trait Group {
	/**
	 * Add user's groups
	 *
	 * @param int|int[]		$group	Group id
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function add_groups ($group, $user = false) {
		$user	= (int)$user ?: $this->id;
		if (!$user || $user == User::GUEST_ID) {
			return false;
		}
		$groups	= $this->get_groups($user);
		foreach ((array)_int($group) as $g) {
			$groups[]	= $g;
		}
		unset($g);
;		return $this->set_groups($groups, $user);
	}
	/**
	 * Get user's groups
	 *
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return bool|int[]
	 */
	function get_groups ($user = false) {
		$user	= (int)$user ?: $this->id;
		if (!$user || $user == User::GUEST_ID) {
			return false;
		}
		return $this->cache->get("groups/$user", function () use ($user) {
			return $this->db()->qfas(
				"SELECT `group`
				FROM `[prefix]users_groups`
				WHERE `id` = '$user'
				ORDER BY `priority` DESC"
			);
		});
	}
	/**
	 * Set user's groups
	 *
	 * @param int[]		$groups
	 * @param bool|int	$user
	 *
	 * @return bool
	 */
	function set_groups ($groups, $user = false) {
		$user		= (int)$user ?: $this->id;
		if (!$user) {
			return false;
		}
		if (!empty($groups) && is_array_indexed($groups)) {
			foreach ($groups as $i => &$group) {
				if (!($group = (int)$group)) {
					unset($groups[$i]);
				}
			}
		}
		unset($i, $group);
		$existing	= $this->get_groups($user);
		$return		= true;
		$insert		= array_diff($groups, $existing);
		$delete		= array_diff($existing, $groups);
		unset($existing);
		if (!empty($delete)) {
			$delete	= implode(', ', $delete);
			$return	= $return && $this->db_prime()->q(
				"DELETE FROM `[prefix]users_groups`
				WHERE
					`id`	='$user' AND
					`group`	IN ($delete)"
			);
		}
		unset($delete);
		if (!empty($insert)) {
			foreach ($insert as &$i) {
				$i = [$user, $i];
			}
			unset($i);
			$return	= $return && $this->db_prime()->insert(
				"INSERT INTO `[prefix]users_groups`
					(
						`id`,
						`group`
					) VALUES (
						'%s',
						'%s'
					)",
				$insert
			);
		}
		unset($insert);
		$update		= [];
		foreach ($groups as $i => $group) {
			$update[] =
				"UPDATE `[prefix]users_groups`
				SET `priority` = '$i'
				WHERE
					`id`	= '$user' AND
					`group`	= '$group'
				LIMIT 1";
		}
		$return		= $return && $this->db_prime()->q($update);
		$Cache		= $this->cache;
		unset(
			$Cache->{"groups/$user"},
			$Cache->{"permissions/$user"}
		);
		return $return;
	}
	/**
	 * Delete user's groups
	 *
	 * @param int|int[]		$group	Group id
	 * @param bool|int		$user	If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_groups ($group, $user = false) {
		$user	= (int)$user ?: $this->id;
		if (!$user || $user == User::GUEST_ID) {
			return false;
		}
		$groups	= array_diff(
			$this->get_groups($user),
			(array)_int($group)
		);
		return $this->set_groups($groups, $user);
	}
}
