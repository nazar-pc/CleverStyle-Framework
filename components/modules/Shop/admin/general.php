<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;

include __DIR__.'/save.php';

$L                   = new Prefix('shop_');
$Config              = Config::instance();
$Index               = Index::instance();
$Page                = Page::instance();
$module_data         = $Config->module('Shop');
$Index->apply_button = false;
$Page->title($L->general);
$Order_statuses = Order_statuses::instance();
$order_statuses = $Order_statuses->get($Order_statuses->get_all());
$Index->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		[
			$L->price_formatting,
			h::{'input[name=price_formatting][required]'}([
				'value'       => $module_data->price_formatting,
				'placeholder' => '$%s USD'
			])
		],
		[
			$L->items_per_page,
			h::{'input[name=items_per_page][required][type=number][min=1]'}([
				'value' => $module_data->items_per_page
			])
		],
		[
			$L->items_per_page_admin,
			h::{'input[name=items_per_page_admin][required][type=number][min=1]'}([
				'value' => $module_data->items_per_page_admin
			])
		],
		[
			h::info('shop_automatically_reduce_in_stock_value'),
			h::{'radio[name=automatically_reduce_in_stock_value]'}([
				'checked' => $module_data->automatically_reduce_in_stock_value,
				'value'   => [0, 1],
				'in'      => [$L->no, $L->yes]
			])
		],
		[
			h::info('shop_default_order_status'),
			h::{'select[name=default_order_status]'}(
				[
					'in'    => array_column($order_statuses, 'title'),
					'value' => array_column($order_statuses, 'id')
				],
				[
					'selected' => $module_data->default_order_status
				]
			)
		],
		[
			h::info('shop_default_paid_order_status'),
			h::{'select[name=default_paid_order_status]'}(
				[
					'in'    => array_column($order_statuses, 'title'),
					'value' => array_column($order_statuses, 'id')
				],
				[
					'selected' => $module_data->default_paid_order_status
				]
			)
		]
	)
);
