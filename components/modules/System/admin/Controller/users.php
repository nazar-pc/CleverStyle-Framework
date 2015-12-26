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
	h;

trait users {
	static function users_general () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_users_general()
		);
	}
	static function users_groups () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_groups_list()
		);
	}
	static function users_mail () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_mail()
		);
	}
	static function users_permissions () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_permissions_list()
		);
	}
	static function users_security () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_security()
		);
	}
	static function users_users () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_users_list()
		);
	}
}
