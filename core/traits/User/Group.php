<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\User;
use cs\User;

/**
 * Trait that contains all methods from <i>>cs\User</i> for working with user groups
 *
 * @property int              $id
 * @property \cs\Cache\Prefix $cache
 *
 * @method \cs\DB\_Abstract db()
 * @method \cs\DB\_Abstract db_prime()
 */
trait Group {
	/**
	 * Add user's groups
	 *
	 * @param int|int[] $group Group id
	 * @param false|int $user  If not specified - current user assumed
	 *
	 * @return bool
	 */
	function add_groups ($group, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user || $user == User::GUEST_ID) {
			return false;
		}
		$groups = $this->get_groups($user) ?: [];
		foreach ((array)_int($group) as $g) {
			$groups[] = $g;
		};
		return $this->set_groups($groups, $user);
	}
	/**
	 * Get user's groups
	 *
	 * @param false|int $user If not specified - current user assumed
	 *
	 * @return false|int[]
	 */
	function get_groups ($user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user || $user == User::GUEST_ID) {
			return false;
		}
		return $this->cache->get(
			"groups/$user",
			function () use ($user) {
				return $this->db()->qfas(
					"SELECT `group`
					FROM `[prefix]users_groups`
					WHERE `id` = '$user'
					ORDER BY `priority` DESC"
				);
			}
		);
	}
	/**
	 * Set user's groups
	 *
	 * @param int[]     $groups
	 * @param false|int $user
	 *
	 * @return bool
	 */
	function set_groups ($groups, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user) {
			return false;
		}
		if (!$groups) {
			return (bool)$this->db_prime()->q(
				"DELETE FROM `[prefix]users_groups`
				WHERE
					`id`	='$user'"
			);
		}
		$groups          = _int($groups);
		$groups_imploded = implode(', ', $groups);
		$return          = $this->db_prime()->q(
			"DELETE FROM `[prefix]users_groups`
			WHERE
				`id`	= '$user' AND
				`group`	NOT IN ($groups_imploded)"
		);
		unset($groups_imploded);
		$insert_update = [];
		foreach ($groups as $priority => $group) {
			$insert_update[] = [$group, $priority];
		}
		$return =
			$return &&
			$this->db_prime()->insert(
				"INSERT INTO `[prefix]users_groups`
					(
						`id`,
						`group`,
						`priority`
					) VALUES (
						'$user',
						'%d',
						'%d'
					)
				ON DUPLICATE KEY UPDATE `priority` = VALUES(`priority`)",
				$insert_update
			);
		unset($insert_update);
		$Cache = $this->cache;
		unset(
			$Cache->{"groups/$user"},
			$Cache->{"permissions/$user"}
		);
		return $return;
	}
	/**
	 * Delete user's groups
	 *
	 * @param int|int[] $group Group id
	 * @param false|int $user  If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_groups ($group, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$user || $user == User::GUEST_ID) {
			return false;
		}
		$groups = array_diff(
			$this->get_groups($user),
			(array)_int($group)
		);
		return $this->set_groups($groups, $user);
	}
}
