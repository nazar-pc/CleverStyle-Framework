<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blockchain_payment;
use
	cs\Event,
	cs\Language\Prefix,
	cs\Menu,
	cs\Request;

if (!function_exists(__NAMESPACE__.'\\add_menu_section_item')) {
	function add_menu_section_item ($section) {
		$L     = new Prefix('blockchain_payment_');
		$Menu  = Menu::instance();
		$route = Request::instance()->route_path;
		$Menu->add_item(
			'Blockchain_payment',
			$L->$section,
			[
				'href'    => "admin/Blockchain_payment/$section",
				'primary' => isset($route[0]) && $route[0] == $section
			]
		);
	}
}
Event::instance()->on(
	'admin/System/Menu',
	function () {
		add_menu_section_item('general');
		add_menu_section_item('transactions');
	}
);
