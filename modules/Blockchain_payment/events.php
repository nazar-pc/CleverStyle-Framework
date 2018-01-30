<?php
/**
 * @package  Blockchain payment
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Blockchain_payment;
use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language\Prefix,
	cs\Menu,
	cs\Request,
	cs\Response;

Event::instance()
	->on(
		'System/payment/methods',
		function ($data) {
			$module_data = Config::instance()->module('Blockchain_payment');
			if (
				$module_data->enabled() &&
				$module_data->bitcoin_address &&
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
			$module_data = Config::instance()->module('Blockchain_payment');
			if (!$module_data->enabled() || $data['payment_method'] != 'blockchain_payment:btc') {
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
	)
	->on(
		'admin/System/Menu',
		function () {
			$L       = new Prefix('blockchain_payment_');
			$Menu    = Menu::instance();
			$Request = Request::instance();
			foreach (['general', 'transactions'] as $section) {
				$Menu->add_item(
					'Blockchain_payment',
					$L->$section,
					[
						'href'    => "admin/Blockchain_payment/$section",
						'primary' => $Request->route_path(0) == $section
					]
				);
			}
		}
	)
	->on(
		'admin/System/modules/install/after',
		function ($data) {
			if ($data['name'] != 'Blockchain_payment') {
				return;
			}
			Config::instance()->module('Blockchain_payment')->set(
				[
					'bitcoin_address'        => '',
					'confirmations_required' => 6
				]
			);
		}
	);
