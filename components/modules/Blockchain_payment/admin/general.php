<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
use
	cs\Config,
	cs\Index;
$Index       = Index::instance();
$Config      = Config::instance();
$module_data = $Config->module('Blockchain_payment');
if (isset($_POST['bitcoin_address'], $_POST['bitcoin_address'])) {
	$module_data->set(
		[
			'bitcoin_address'        => $_POST['bitcoin_address'],
			'confirmations_required' => min(1, $_POST['confirmations_required'])
		]
	);
	$Index->save(true);
}
$Index->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		[
			h::info('blockchain_payment_bitcoin_address'),
			h::input(
				[
					'name'  => 'bitcoin_address',
					'value' => $module_data->bitcoin_address
				]
			)
		],
		[
			h::info('blockchain_payment_confirmations_required'),
			h::input(
				[
					'name'  => 'confirmations_required',
					'value' => $module_data->confirmations_required,
					'type'  => 'number',
					'min'   => 1
				]
			)
		]
	)
);
