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
	cs\Language,
	cs\Language\Prefix,
	cs\Page,
	cs\User;
$make_url = function ($arguments) {
	$base_url = 'admin/Shop/orders/?';
	return $base_url.http_build_query(array_merge((array)$_GET, $arguments));
};

$make_header = function ($title, $field) use ($make_url) {
	$order_by = @$_GET['order_by'] ?: 'created';
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

Index::instance()->buttons = false;
$L                         = new Prefix('shop_');
$Language                  = Language::instance();
$Page                      = Page::instance();
$Categories                = Categories::instance();
$Items                     = Items::instance();
$Orders                    = Orders::instance();
$Order_statuses            = Order_statuses::instance();
$Shipping_types            = Shipping_types::instance();
$page                      = @$_GET['page'] ?: 1;
$count                     = @$_GET['count'] ?: Config::instance()->module('Shop')->items_per_page_admin;
$orders                    = $Orders->get(
	$Orders->search(
		(array)$_GET,
		$page,
		$count,
		@$_GET['order_by'] ?: 'date',
		@$_GET['asc']
	)
);
$orders_total              = $Orders->search(
	[
		'total_count' => 1
	] + (array)$_GET,
	$page,
	$count,
	@$_GET['order_by'] ?: 'date',
	@$_GET['asc']
);
$module_path               = path($L->shop);
$items_path                = path($L->items);
$Page->title($L->orders);
$Page->content(
	h::{'h3.cs-text-center'}($L->orders).
	h::{'table.cs-table[list]'}(
		h::{'tr th'}(
			$make_header('id', 'id'),
			$make_header($L->datetime, 'date'),
			$make_header($L->user, 'user'),
			$L->order_items,
			$make_header($L->shipping_type, 'shipping_type'),
			$make_header($L->status, 'status'),
			$L->comment,
			$L->action
		).
		h::tr(
			array_map(
				function ($order) use ($L, $Language, $Categories, $Items, $Order_statuses, $Orders, $Shipping_types, $module_path, $items_path) {
					$order_status = $Order_statuses->get($order['status']);
					$date         = $L->to_locale(
						date($Language->{TIME - $order['date'] < 24 * 3600 ? '_time' : '_datetime_long'}, $order['date'])
					);
					$username     = User::instance()->username($order['user']);
					return h::td(
						[
							$order['id'],
							$date,
							h::a(
								$username." ($order[shipping_username]) ".h::br().$order['shipping_phone'],
								[
									'href' => "admin/Shop/orders/?user=$order[user]"
								]
							),
							implode(
								h::br(),
								array_map(
									function ($item) use ($Categories, $Items, $module_path, $items_path) {
										$item = $Items->get($item['item']);
										return h::a(
											$item['title'],
											[
												'href'   => "$module_path/$items_path/".
															path($Categories->get($item['category'])['title']).
															'/'.
															path($item['title']).
															":$item[id]",
												'target' => '_blank'
											]
										);
									},
									$Orders->get_items($order['id'])
								)
							),
							h::a(
								$Shipping_types->get($order['shipping_type'])['title'],
								[
									'href' => "admin/Shop/orders/?shipping_type=$order[shipping_type]"
								]
							),
							h::a(
								$order_status['title'],
								[
									'href' => "admin/Shop/orders/?status=$order[status]"
								]
							),
							nl2br($order['comment']),
							h::{'button.cs-shop-order-statuses-history[is=cs-button]'}(
								$L->statuses_history,
								[
									'data-id' => $order['id']
								]
							).
							h::{'button.cs-shop-order-edit[is=cs-button]'}(
								$L->edit,
								[
									'data-id'       => $order['id'],
									'data-username' => $username,
									'data-date'     => $date
								]
							).
							h::{'button.cs-shop-order-delete[is=cs-button]'}(
								$L->delete,
								[
									'data-id' => $order['id']
								]
							)
						],
						[
							'style' => $order_status['color'] ? "background: $order_status[color]" : ''
						]
					);
				},
				$orders
			) ?: false
		)
	).
	pages(
		$page,
		ceil($orders_total / $count),
		function ($page) use ($make_url) {
			return $make_url(
				[
					'page' => $page
				]
			);
		},
		true
	).
	h::{'p button.cs-shop-order-add[is=cs-button]'}($L->add)
);
