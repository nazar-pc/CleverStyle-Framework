<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;

include __DIR__.'/save.php';

$L           = new Prefix('shop_');
$Config      = Config::instance();
$Index       = Index::instance();
$Page        = Page::instance();
$module_data = $Config->module('Shop');
$Page->title($L->general);
$Order_statuses = Order_statuses::instance();
$order_statuses = $Order_statuses->get($Order_statuses->get_all());
$currencies     = file_get_json(__DIR__.'/../currencies_codes.json');
$Index->content(
	h::{'table.cs-table[right-left] tr| td'}(
		[
			$L->currency,
			h::{'select[is=cs-select][name=currency]'}(
				h::optgroup(
					[
						'in'    => array_values(
							array_map(
								function ($key, $value) {
									return "$key: $value";
								},
								array_keys($currencies['regular']),
								array_values($currencies['regular'])
							)
						),
						'value' => array_keys($currencies['regular'])
					],
					[
						'selected' => $module_data->currency,
						'label'    => h::prepare_attr_value($L->regular_currencies)
					]
				).
				h::optgroup(
					[
						'in'    => array_values(
							array_map(
								function ($key, $value) {
									return "$key: $value";
								},
								array_keys($currencies['crypto']),
								array_values($currencies['crypto'])
							)
						),
						'value' => array_keys($currencies['crypto'])
					],
					[
						'selected' => $module_data->currency,
						'label'    => h::prepare_attr_value($L->cryptocurrencies)
					]
				)
			)
		],
		[
			$L->price_formatting,
			h::{'input[name=price_formatting][required]'}(
				[
					'value'       => $module_data->price_formatting,
					'placeholder' => '$%s USD'
				]
			)
		],
		[
			$L->items_per_page,
			h::{'input[name=items_per_page][required][type=number][min=1]'}(
				[
					'value' => $module_data->items_per_page
				]
			)
		],
		[
			$L->items_per_page_admin,
			h::{'input[name=items_per_page_admin][required][type=number][min=1]'}(
				[
					'value' => $module_data->items_per_page_admin
				]
			)
		],
		[
			h::info('shop_allow_guests_orders'),
			h::{'radio[name=allow_guests_orders]'}(
				[
					'checked' => $module_data->allow_guests_orders,
					'value'   => [0, 1],
					'in'      => [$L->no, $L->yes]
				]
			)
		],
		[
			h::info('shop_automatically_reduce_in_stock_value'),
			h::{'radio[name=automatically_reduce_in_stock_value]'}(
				[
					'checked' => $module_data->automatically_reduce_in_stock_value,
					'value'   => [0, 1],
					'in'      => [$L->no, $L->yes]
				]
			)
		],
		[
			h::info('shop_default_order_status'),
			h::{'select[is=cs-select][name=default_order_status]'}(
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
			h::{'select[is=cs-select][name=default_paid_order_status]'}(
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
