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
	cs\Config,
	cs\Event,
	cs\Language\Prefix;

Event::instance()
	->on(
		'System/payment/methods',
		function ($data) {
			if (
				$data['currency'] == 'BTC' ||
				in_array($data['currency'], file_get_json(__DIR__.'/../convertible_currencies.json'))
			) {
				$L                                         = new Prefix('blockchain_payment_');
				$data['methods']['blockchain_payment:btc'] = [
					'title'       => 'BTC: blockchain.info',
					'description' => $L->pay_in_bitcoin
				];
			}
		}
	);
