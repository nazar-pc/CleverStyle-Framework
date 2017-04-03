<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Language\Prefix,
	cs\Page;

$make_url = function ($arguments) {
	$base_url = 'admin/Shop/items/?';
	return $base_url.http_build_query(array_merge((array)$_GET, $arguments));
};

$make_header = function ($title, $field) use ($make_url) {
	$order_by = $_GET['order_by'] ?? 'created';
	$icon     = $order_by == $field ? h::icon(@$_GET['asc'] ? 'caret-up' : 'caret-down') : '';
	$asc      = $order_by == $field ? !@$_GET['asc'] : false;
	return h::a(
		"$title $icon",
		[
			'href' => $make_url(
				[
					'order_by' => $field,
					'asc'      => $asc,
					'page'     => 1
				]
			)
		]
	);
};

$L           = new Prefix('shop_');
$Categories  = Categories::instance();
$Items       = Items::instance();
$page        = $_GET['page'] ?? 1;
$module_data = Config::instance()->module('Shop');
$count       = $_GET['count'] ?? $module_data->items_per_page_admin;
$items       = $Items->get(
	$Items->search(
		(array)$_GET,
		$page,
		$count,
		$_GET['order_by'] ?? 'id',
		@$_GET['asc']
	) ?: []
);
$items_total = $Items->search(
	[
		'total_count' => 1
	] + (array)$_GET,
	$page,
	$count,
	$_GET['order_by'] ?? 'id',
	@$_GET['asc']
) ?: 0;
Page::instance()
	->title($L->items)
	->content(
		h::{'h2.cs-text-center'}($L->items).
		h::{'table.cs-table[list]'}(
			h::{'tr th'}(
				$make_header('id', 'id'),
				$L->title,
				$make_header($L->category, 'category'),
				$make_header($L->price, 'price'),
				$make_header($L->in_stock, 'in_stock'),
				$make_header($L->listed, 'listed'),
				$L->action
			).
			h::{'tr| td'}(
				array_map(
					function ($item) use ($L, $Categories, $module_data) {
						return [
							[
								$item['id'],
								$item['title'],
								h::a(
									$Categories->get($item['category'])['title'],
									[
										'href' => "admin/Shop/items/?category=$item[category]"
									]
								),
								sprintf($module_data->price_formatting, $item['price']),
								$item['in_stock'] ?: ($item['soon'] ? $L->available_soon : 0),
								h::a(
									h::icon($item['listed'] ? 'check' : 'minus'),
									[
										'href' => "admin/Shop/items/?listed=$item[listed]"
									]
								),
								h::{'cs-button button.cs-shop-item-edit'}(
									$L->edit,
									[
										'data-id' => $item['id']
									]
								).
								h::{'cs-button button.cs-shop-item-delete'}(
									$L->delete,
									[
										'data-id' => $item['id']
									]
								)
							],
							[
								'class' => $item['listed'] ? 'cs-block-success cs-text-success' :
									($item['in_stock'] || $item['soon'] ? 'cs-block-warning cs-text-warning' : 'cs-block-error cs-text-error')
							]
						];
					},
					$items ?: []
				) ?: false
			)
		).
		pages(
			$page,
			ceil($items_total / $count),
			function ($page) use ($make_url) {
				return $make_url(
					[
						'page' => $page
					]
				);
			},
			true
		).
		h::{'p cs-button button.cs-shop-item-add'}($L->add)
	);
