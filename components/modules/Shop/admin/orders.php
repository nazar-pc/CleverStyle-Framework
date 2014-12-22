<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
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
function make_url ($arguments) {
	$base_url = 'admin/Shop/orders/?';
	return $base_url.http_build_query(array_merge($_GET, $arguments));
}

function make_header ($title, $field) {
	$order_by = @$_GET['order_by'] ?: 'date';
	return h::a(
		"$title ".
		($order_by == $field ? h::icon(@$_GET['asc'] ? 'caret-up' : 'caret-down') : ''),
		[
			'href' => make_url([
				'order_by' => $field,
				'asc'      => $order_by == $field ? !@$_GET['asc'] : false,
				'page'     => 1
			])
		]
	);
}

Index::instance()->buttons = false;
$L                         = new Prefix('shop_');
$Language                  = Language::instance();
$Page                      = Page::instance();
$Page->title($L->orders);
$Items          = Items::instance();
$Orders         = Orders::instance();
$Order_statuses = Order_statuses::instance();
$Shipping_types = Shipping_types::instance();
$page           = @$_GET['page'] ?: 1;
$count          = @$_GET['count'] ?: 20;
$orders         = $Orders->get($Orders->search(
	$_GET,
	$page,
	$count,
	@$_GET['order_by'] ?: 'date',
	@$_GET['asc']
));
$orders_total   = $Orders->search(
	[
		'total_count' => 1
	] + $_GET,
	$page,
	$count,
	@$_GET['order_by'] ?: 'date',
	@$_GET['asc']
);
$Page->content(
	h::{'h3.uk-lead.cs-center'}($L->orders).
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			make_header('id', 'id'),
			make_header($L->datetime, 'date'),
			make_header($L->user, 'user'),
			$L->order_items,
			make_header($L->shipping_type, 'shipping_type'),
			make_header($L->status, 'status'),
			$L->comment,
			$L->action
		).
		h::{'cs-table-row'}(array_map(
			function ($order) use ($L, $Language, $Items, $Order_statuses, $Orders, $Shipping_types) {
				$order_status = $Order_statuses->get($order['status']);
				$date         = $L->to_locale(
					date($Language->{TIME - $order['date'] < 24 * 3600 ? '_time' : '_datetime_long'}, $order['date'])
				);
				$username     = User::instance()->username($order['user']);
				return h::cs_table_cell(
					[
						$order['id'],
						$date,
						h::a(
							$username.h::br().$order['shipping_phone'],
							[
								'href' => "admin/Shop/orders/?user=$order[user]"
							]
						),
						implode(
							h::br(),
							array_map(
								function ($item) use ($Items) {
									return $Items->get($item['item'])['title']; // TODO links to items
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
						$order['comment'],
						h::{'button.uk-button.cs-shop-order-details'}( // TODO details page/modal
							$L->details,
							[
								'data-id' => $order['id']
							]
						).
						h::{'button.uk-button.cs-shop-order-edit'}(
							$L->edit,
							[
								'data-id'       => $order['id'],
								'data-username' => $username,
								'data-date'     => $date
							]
						).
						h::{'button.uk-button.cs-shop-order-delete'}(
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
		) ?: false)
	).
	pages($page, ceil($orders_total / $count), function ($page) {
		return make_url([
			'page' => $page
		]);
	}, true).
	h::{'p button.uk-button.cs-shop-order-add'}($L->add)
);
