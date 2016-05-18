<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\User;
use
	cs\User;

/**
 * Trait that contains all methods for `cs\User` for working with user's data
 *
 * @property \cs\Cache\Prefix $cache
 * @property int              $id
 *
 * @method \cs\DB\_Abstract db()
 * @method \cs\DB\_Abstract db_prime()
 */
trait Data {
	/**
	 * Getting additional data item(s) of specified user
	 *
	 * @param string|string[] $item
	 * @param false|int       $user If not specified - current user assumed
	 *
	 * @return false|string|mixed[]
	 */
	function get_data ($item, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$item || $user == User::GUEST_ID) {
			return false;
		}
		$data = $this->cache->{"data/$user"} ?: [];
		if (is_array($item)) {
			$result = [];
			$absent = [];
			foreach ($item as $i) {
				if (isset($data[$i])) {
					$result[$i] = $data[$i];
				} else {
					$absent[] = $i;
				}
			}
			if ($absent) {
				$absent = implode(
					',',
					$this->db()->s($absent)
				);
				$absent = array_column(
					$this->db()->qfa(
						"SELECT `item`, `value`
						FROM `[prefix]users_data`
						WHERE
							`id`	= '$user' AND
							`item`	IN($absent)"
					),
					'value',
					'item'
				);
				foreach ($absent as &$a) {
					$a = _json_decode($a);
					if ($a === null) {
						$a = false;
					}
				}
				unset($a);
				$result += $absent;
				$data += $absent;
				$this->cache->{"data/$user"} = $data;
			}
			return $result;
		}
		if ($data === false || !isset($data[$item])) {
			if (!is_array($data)) {
				$data = [];
			}
			$data[$item] = _json_decode(
				$this->db()->qfs(
					"SELECT `value`
					FROM `[prefix]users_data`
					WHERE
						`id`	= '$user' AND
						`item`	= '%s'",
					$item
				)
			);
			if ($data[$item] === null) {
				$data[$item] = false;
			}
			$this->cache->{"data/$user"} = $data;
		}
		return $data[$item];
	}
	/**
	 * Setting additional data item(s) of specified user
	 *
	 * @param array|string $item Item-value array may be specified for setting several items at once
	 * @param mixed|null   $value
	 * @param false|int    $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function set_data ($item, $value = null, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$item || $user == User::GUEST_ID) {
			return false;
		}
		if (!is_array($item)) {
			$item = [
				$item => $value
			];
		}
		$params = [];
		foreach ($item as $i => $v) {
			$params[] = [$i, _json_encode($v)];
		}
		unset($i, $v);
		$result = $this->db_prime()->insert(
			"REPLACE INTO `[prefix]users_data`
				(
					`id`,
					`item`,
					`value`
				) VALUES (
					$user,
					'%s',
					'%s'
				)",
			$params
		);
		$this->cache->del("data/$user");
		return $result;
	}
	/**
	 * Deletion of additional data item(s) of specified user
	 *
	 * @param string|string[] $item
	 * @param false|int       $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_data ($item, $user = false) {
		$user = (int)$user ?: $this->id;
		if (!$item || $user == User::GUEST_ID) {
			return false;
		}
		$item   = implode(
			',',
			$this->db_prime()->s((array)$item)
		);
		$result = $this->db_prime()->q(
			"DELETE FROM `[prefix]users_data`
			WHERE
				`id`	= '$user' AND
				`item`	IN($item)"
		);
		$this->cache->del("data/$user");
		return (bool)$result;
	}
}
