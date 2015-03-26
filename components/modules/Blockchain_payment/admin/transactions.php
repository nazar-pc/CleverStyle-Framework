<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blockchain_payment;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language,
	cs\Language\Prefix,
	cs\Page,
	cs\User;
function make_url ($arguments) {
	$base_url = 'admin/Blockchain_payment/transactions?';
	return $base_url.http_build_query(array_merge((array)$_GET, $arguments));
}

function make_header ($title, $field) {
	$order_by = @$_GET['order_by'] ?: 'created';
	return h::a(
		"$title ".
		($order_by == $field ? h::icon(@$_GET['asc'] ? 'caret-up' : 'caret-down') : ''),
		[
			'href' => make_url(
				[
					'order_by' => $field,
					'asc'      => $order_by == $field ? !@$_GET['asc'] : false,
					'page'     => 1
				]
			)
		]
	);
}

Index::instance()->buttons = false;
$L                         = new Prefix('blockchain_payment_');
$Language                  = Language::instance();
$Page                      = Page::instance();
$Transactions              = Transactions::instance();
$page                      = @$_GET['page'] ?: 1;
$count                     = @$_GET['count'] ?: 100;
$transactions              = $Transactions->get(
	$Transactions->search(
		(array)$_GET,
		$page,
		$count,
		@$_GET['order_by'] ?: 'created',
		@$_GET['asc']
	)
);
$transactions_total        = $Transactions->search(
	[
		'total_count' => 1
	] + (array)$_GET,
	$page,
	$count,
	@$_GET['order_by'] ?: 'created',
	@$_GET['asc']
);
$Page->title($L->transactions);
$Page->content(
	h::{'h3.uk-lead.cs-center'}($L->transactions).
	h::{'table.cs-table[list][with-header]'}(
		h::{'tr.cs-table-row td.cs-table-cell'}(
			make_header('id', 'id'),
			make_header($L->amount, 'amount'),
			make_header($L->currency, 'currency'),
			make_header($L->amount_btc, 'amount_btc'),
			make_header($L->user, 'user'),
			make_header($L->created, 'created'),
			make_header($L->paid, 'paid'),
			make_header($L->confirmed, 'confirmed')
		).
		h::{'tr.cs-table-row'}(
			array_map(
				function ($transaction) use ($L, $Language, $Transactions) {
					$created   = $transaction['created']
						? $L->to_locale(
							date($Language->{TIME - $transaction['created'] < 24 * 3600 ? '_time' : '_datetime_long'}, $transaction['created'])
						)
						: '-';
					$paid      = $transaction['paid']
						? $L->to_locale(
							date($Language->{TIME - $transaction['paid'] < 24 * 3600 ? '_time' : '_datetime_long'}, $transaction['paid'])
						)
						: '-';
					$confirmed = $transaction['confirmed']
						? $L->to_locale(
							date($Language->{TIME - $transaction['confirmed'] < 24 * 3600 ? '_time' : '_datetime_long'}, $transaction['confirmed'])
						)
						: '-';
					$username  = User::instance()->username($transaction['user']);
					$class     = $transaction['confirmed'] ? 'uk-alert-success' : ($transaction['paid'] ? 'uk-alert-warning' : 'uk-alert-danger');
					$tag       = "td.cs-table-cell.$class";
					return [
						[
							h::$tag(
								$transaction['id'],
								$transaction['amount'],
								$transaction['currency'],
								$transaction['amount_btc'],
								h::a(
									$username,
									[
										'href' => "admin/Blockchain_payment/transactions/?user=$transaction[user]"
									]
								),
								$created,
								$paid,
								$confirmed
							),
							[
								'style' => 'border-bottom: none;'
							]
						],
						h::$tag(
							"$L->module: ".
							h::a(
								$transaction['module'],
								[
									'href' => "admin/Blockchain_payment/transactions/?module=$transaction[module]"
								]
							).
							" $L->purpose: $transaction[purpose]".
							h::br().
							"$L->destination_address: ".
							h::{'a[target=_blank]'}(
								$transaction['destination'],
								[
									'href' => "https://blockchain.info/address/$transaction[destination]"
								]
							).
							" $L->intermediate_address: ".
							h::{'a[target=_blank]'}(
								$transaction['input_address'],
								[
									'href' => "https://blockchain.info/address/$transaction[input_address]"
								]
							).
							h::br().
							" $L->transaction_hash: ".
							h::{'a[target=_blank]'}(
								$transaction['transaction_hash'],
								[
									'href' => "https://blockchain.info/tx/$transaction[transaction_hash]"
								]
							).
							" $L->intermediate_transaction_hash: ".
							h::{'a[target=_blank]'}(
								$transaction['input_transaction_hash'],
								[
									'href' => "https://blockchain.info/tx/$transaction[input_transaction_hash]"
								]
							).
							h::br().
							nl2br($transaction['description']),
							[
								'colspan' => 8
							]
						)
					];
				},
				$transactions
			) ?: false
		)
	).
	pages(
		$page,
		ceil($transactions_total / $count),
		function ($page) {
			return make_url(
				[
					'page' => $page
				]
			);
		},
		true
	)
);
