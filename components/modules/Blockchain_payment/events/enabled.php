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
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language\Prefix,
	cs\Response;

Event::instance()
	->on(
		'System/payment/methods',
		function ($data) {
			if (
				Config::instance()->module('Blockchain_payment')->bitcoin_address &&
				(
					$data['currency'] == 'BTC' ||
					in_array($data['currency'], file_get_json(__DIR__.'/../convertible_currencies.json'))
				)
			) {
				$L                                         = new Prefix('blockchain_payment_');
				$data['methods']['blockchain_payment:btc'] = [
					'title'       => "BTC: $L->pay_in_bitcoin",
					'description' => ''
				];
			}
		}
	)
	->on(
		'System/payment/execute',
		function ($data) {
			if ($data['payment_method'] != 'blockchain_payment:btc') {
				return true;
			}
			$id = Transactions::instance()->add(
				$data['amount'],
				$data['currency'],
				$data['user'],
				$data['module'],
				$data['purpose'],
				$data['description']
			);
			if (!$id) {
				throw new ExitException(500);
			}
			Response::instance()->redirect("/Blockchain_payment/$id", 307);
			return false;
		}
	);
