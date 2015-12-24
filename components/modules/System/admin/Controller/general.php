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

trait general {
	static function general_about_server ($route_ids, $route_path) {
		$Index       = Index::instance();
		$Index->form = false;
		if (isset($route_path[2])) {
			interface_off();
			switch ($route_path[2]) {
				case 'phpinfo':
					$Index->Content = ob_wrapper(
						function () {
							phpinfo();
						}
					);
					break;
				case 'readme.html':
					$Index->Content = file_get_contents(DIR.'/readme.html');
			}
			return;
		}
		$Index->content(
			h::cs_system_admin_about_server()
		);
	}
	static function general_appearance () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_themes()
		);
	}
	static function general_languages () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_languages()
		);
	}
	static function general_optimization () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_optimization()
		);
	}
	static function general_site_info () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_site_info()
		);
	}
	static function general_system () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_system()
		);
	}
}
