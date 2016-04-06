<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\ExitException,
	cs\Group,
	cs\Permission,
	cs\User;

trait permissions {
	/**
	 * Get array of permissions data or data of specific permission if id specified
	 *
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function admin_permissions___get ($Request) {
		$Permission = Permission::instance();
		if (isset($Request->route_ids[0])) {
			$result = $Permission->get($Request->route_ids[0]);
			if (!$result) {
				throw new ExitException(404);
			}
		} else {
			$result = $Permission->get_all();
		}
		return $result;
	}
	/**
	 * Add new permission
	 *
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @throws ExitException
	 */
	static function admin_permissions___post ($Request, $Response) {
		$data = $Request->data('group', 'label');
		if (!$data) {
			throw new ExitException(400);
		}
		if (Permission::instance()->add(...array_values($data))) {
			$Response->code = 201;
		} else {
			throw new ExitException(500);
		}
	}
	/**
	 * Update permission's data
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_permissions___put ($Request) {
		$id   = $Request->route_ids(0);
		$data = $Request->data('group', 'label');
		if (!$id || !$data) {
			throw new ExitException(400);
		}
		if (!Permission::instance()->set($id, ...array_values($data))) {
			throw new ExitException(500);
		}
	}
	/**
	 * Delete permission
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_permissions___delete ($Request) {
		if (!isset($Request->route_ids[0])) {
			throw new ExitException(400);
		}
		if (!Permission::instance()->del($Request->route_ids[0])) {
			throw new ExitException(500);
		}
	}
	/**
	 * Get permissions for specific item
	 *
	 * @param \cs\Request $Request
	 *
	 * @return array
	 *
	 * @throws ExitException
	 */
	static function admin_permissions_for_item_get ($Request) {
		$data = $Request->data('group', 'label');
		if (!$data) {
			throw new ExitException(400);
		}
		$User       = User::instance();
		$Permission = Permission::instance();
		$permission = $Permission->get(null, ...array_values($data));
		$data       = [
			'groups' => [],
			'users'  => []
		];
		if ($permission) {
			$data['groups'] = array_column(
				$User->db()->qfa(
					"SELECT
						`id`,
						`value`
					FROM `[prefix]groups_permissions`
					WHERE
						`permission`	= '%s'",
					$permission[0]['id']
				) ?: [],
				'value',
				'id'
			);
			$data['users']  = array_column(
				$User->db()->qfa(
					"SELECT
						`id`,
						`value`
					FROM `[prefix]users_permissions`
					WHERE
						`permission`	= '%s'",
					$permission[0]['id']
				) ?: [],
				'value',
				'id'
			);
		}
		return [
			'groups' => (object)$data['groups'],
			'users'  => (object)$data['users']
		];
	}
	/**
	 * Get permissions for specific item
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_permissions_for_item_post ($Request) {
		$data = $Request->data('group', 'label');
		if (!$data) {
			throw new ExitException(400);
		}
		$Group      = Group::instance();
		$Permission = Permission::instance();
		$User       = User::instance();
		$permission = $Permission->get(null, ...array_values($data));
		// We'll create permission if needed
		$permission = $permission
			? $permission[0]['id']
			: $Permission->add(...array_values($data));
		if (!$permission) {
			throw new ExitException(500);
		}
		$result = true;
		foreach ($Request->data('groups') ?: [] as $group => $value) {
			$result = $result && $Group->set_permissions([$permission => $value], $group);
		}
		foreach ($Request->data('users') ?: [] as $user => $value) {
			$result = $result && $User->set_permissions([$permission => $value], $user);
		}
		if (!$result) {
			throw new ExitException(500);
		}
	}
}
