<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
 */
namespace cs;
if (isset($_POST['save'])) {
	$module_data                                      = Config::instance()->module('Shop');
	$module_data->price_formatting                    = xap($_POST['price_formatting']);
	$module_data->items_per_page                      = (int)$_POST['items_per_page'];
	$module_data->items_per_page_admin                = (int)$_POST['items_per_page_admin'];
	$module_data->automatically_reduce_in_stock_value = (int)$_POST['automatically_reduce_in_stock_value'];
	Index::instance()->save(true);
}
