<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
use
	h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix;
$Index               = Index::instance();
$Index->apply_button = false;
$Config              = Config::instance();
$module_data         = $Config->module('Blockchain_payment');
if (isset($_POST['bitcoin_address'])) {
	$module_data->bitcoin_address = $_POST['bitcoin_address'];
	$Index->save(true);
}
$Index->content(
	h::{'cs-table[right-left] cs-table-row cs-table-cell'}(
		h::info('blockchain_payment_bitcoin_address'),
		h::input(
			[
				'name'  => 'bitcoin_address',
				'value' => $module_data->bitcoin_address
			]
		)
	)
);
