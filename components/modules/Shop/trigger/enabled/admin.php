<?php
/**
 * @package    Shop
 * @attribute  modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Menu,
	cs\Trigger;
function add_menu_section_item ($section) {
	$L		= new Prefix('shop_');
	$Menu	= Menu::instance();
	$route	= Index::instance()->route_path;
	$Menu->add_item(
		'Shop',
		$L->$section,
		"admin/Shop/$section",
		[
			'class'	=> $route[0] == $section ? 'uk-active' : false
		]
	);
}
Trigger::instance()->register(
	'admin/System/Menu',
	function () {
		add_menu_section_item('orders');
		add_menu_section_item('categories');
		add_menu_section_item('items');
		add_menu_section_item('attributes');
		add_menu_section_item('order_statuses');
		add_menu_section_item('shipping_types');
	}
);
