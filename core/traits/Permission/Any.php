<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Permission;
/**
 * Class Any with common methods for User and Group classes
 */
trait Any {
	/**
	 * @param int			$id
	 * @param string		$type
	 *
	 * @return array|bool
	 */
	protected function get_any_permissions ($id, $type) {
		if (!($id = (int)$id)) {
			return false;
		}
		switch ($type) {
			case 'user':
				$table	= '[prefix]users_permissions';
				$path	= 'users/';
				break;
			case 'group':
				$table	= '[prefix]group_permissions';
				$path	= 'groups/';
				break;
			default:
				return false;
		}
		return $this->cache->get($path.$id, function () use ($id, $table) {
			$permissions	= false;
			if ($permissions_array = $this->db()->qfa(
				"SELECT
					`permission`,
					`value`
				FROM `$table`
				WHERE `id` = '$id'"
			)) {
				$permissions = [];
				foreach ($permissions_array as $permission) {
					$permissions[$permission['permission']] = (int)(bool)$permission['value'];
				}
			}
			return $permissions;
		});
	}
	/**
	 * @param array		$data
	 * @param int		$id
	 * @param string	$type
	 *
	 * @return bool
	 */
	protected function set_any_permissions ($data, $id, $type) {
		$id		= (int)$id;
		if (!is_array($data) || empty($data) || !$id) {
			return false;
		}
		switch ($type) {
			case 'user':
				$table	= '[prefix]users_permissions';
				$path	= 'users/';
				break;
			case 'group':
				$table	= '[prefix]groups_permissions';
				$path	= 'groups/';
				break;
			default:
				return false;
		}
		$delete	= [];
		foreach ($data as $i => $val) {
			if ($val == -1) {
				$delete[] = (int)$i;
				unset($data[$i]);
			}
		}
		unset($i, $val);
		$return	= true;
		if (!empty($delete)) {
			$delete	= implode(', ', $delete);
			$return	= $this->db_prime()->q(
				"DELETE FROM `$table` WHERE `id` = '$id' AND `permission` IN ($delete)"
			);
		}
		unset($delete);
		if (!empty($data)) {
			$exiting	= $this->get_any_permissions($id, $type);
			if (!empty($exiting)) {
				$update		= [];
				foreach ($exiting as $permission => $value) {
					if (isset($data[$permission]) && $data[$permission] != $value) {
						$value		= (int)(bool)$data[$permission];
						$update[]	=
							"UPDATE `$table`
							SET `value` = '$value'
							WHERE
								`permission`	= '$permission' AND
								`id`			= '$id'";
					}
					unset($data[$permission]);
				}
				unset($exiting, $permission, $value);
				if (!empty($update)) {
					$return = $return && $this->db_prime()->q($update);
				}
				unset($update);
			}
			if (!empty($data)) {
				$insert	= [];
				foreach ($data as $permission => $value) {
					$insert[] = $id.', '.(int)$permission.', '.(int)(bool)$value;
				}
				unset($data, $permission, $value);
				if (!empty($insert)) {
					$insert	= implode('), (', $insert);
					$return	= $return && $this->db_prime()->q(
						"INSERT INTO `$table`
							(
								`id`,
								`permission`,
								`value`
							) VALUES (
								$insert
							)"
					);
				}
			}
		}
		$Cache	= $this->cache;
		unset($Cache->{$path.$id});
		if ($type == 'group') {
			unset($Cache->users);
		}
		return $return;
	}
	/**
	 * @param int		$id
	 * @param string	$type
	 *
	 * @return bool
	 */
	protected function del_any_permissions_all ($id, $type) {
		$id			= (int)$id;
		if (!$id) {
			return false;
		}
		switch ($type) {
			case 'user':
				$table	= '[prefix]users_permissions';
				$path	= 'users/';
			break;
			case 'group':
				$table	= '[prefix]groups_permissions';
				$path	= 'groups/';
			break;
			default:
				return false;
		}
		$return = $this->db_prime()->q("DELETE FROM `$table` WHERE `id` = '$id'");
		if ($return) {
			$Cache	= $this->cache;
			unset($Cache->{$path.$id});
			if ($type == 'group') {
				unset($Cache->users);
			}
			return true;
		}
		return false;
	}
}