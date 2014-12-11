<?php
/**
 * @package    Shop
 * @order_status  modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;

Index::instance()->buttons = false;
$L                         = new Prefix('shop_');
$Page                      = Page::instance();
$Page->title($L->items);
$Categories = Categories::instance();
$Items  = Items::instance();
$all_items  = $Items->get($Items->get_all());
usort($all_items, function ($item1, $item2) {
	return $item1['title'] > $item2['title'] ? 1 : -1;
});
$Page->content(
	h::{'h3.uk-lead.cs-center'}($L->items).
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			'id',
			$L->title,
			$L->category,
			$L->price,
			$L->in_stock,
			$L->listed,
			$L->action
		).
		h::cs_table_row(array_map(
			function ($item) use ($L, $Categories) {
				return h::cs_table_cell(
					[
						$item['id'],
						$item['title'],
						$Categories->get($item['category'])['title'], // TODO link to all items in this category
						$item['price'],
						$item['in_stock'],
						h::icon($item['listed'] ? 'check' : 'minus'), // TODO link to all listed items
						h::{'button.uk-button.cs-shop-item-edit'}(
							$L->edit,
							[
								'data-id' => $item['id']
							]
						).
						h::{'button.uk-button.cs-shop-item-delete'}(
							$L->delete,
							[
								'data-id' => $item['id']
							]
						)
					],
					[
						'class'	=> $item['listed'] ? 'uk-alert-success' : (!$item['listed'] && $item['in_stock'] ? 'uk-alert-warning' : 'uk-alert-danger')
					]
				);
			},
			$all_items
		) ?: false)
	).
	h::{'p button.uk-button.cs-shop-item-add'}($L->add)
);
