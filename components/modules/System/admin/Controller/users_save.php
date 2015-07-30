<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Index,
	cs\User;

trait users_save {
	static function users_users_save () {
		if (!isset($_POST['mode'])) {
			return;
		}
		$Index = Index::instance();
		$User  = User::instance();
		switch ($_POST['mode']) {
			case 'groups':
				if (isset($_POST['user'], $_POST['user']['id'], $_POST['user']['groups']) && $_POST['user']['groups']) {
					$user_id = (int)$_POST['user']['id'];
					if ($_POST['user']['id'] == User::ROOT_ID || in_array(User::BOT_GROUP_ID, (array)$User->get_groups($user_id))) {
						break;
					}
					$groups = _json_decode($_POST['user']['groups']);
					$Index->save(
						$User->set_groups($groups, $user_id)
					);
				}
				break;
		}
	}
}
