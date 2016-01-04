<?php
/**
 * @package   Blockchain payment
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
use
	cs\Config,
	cs\Language,
	cs\Page;

$L           = Language::instance();
$Page        = Page::instance();
$module_data = Config::instance()->module('Blockchain_payment');
if (isset($_POST['bitcoin_address'], $_POST['bitcoin_address'])) {
	if ($module_data->set(
		[
			'bitcoin_address'        => $_POST['bitcoin_address'],
			'confirmations_required' => max(1, $_POST['confirmations_required'])
		]
	)
	) {
		$Page->success($L->changes_saved);
	} else {
		$Page->warning($L->changes_save_error);
	}
}
$Page->content(
	h::{'form[is=cs-form]'}(
		h::{'label info'}('blockchain_payment_bitcoin_address').
		h::{'input[is=cs-input-text]'}(
			[
				'name'  => 'bitcoin_address',
				'value' => $module_data->bitcoin_address
			]
		).
		h::{'label info'}('blockchain_payment_confirmations_required').
		h::{'input[is=cs-input-text]'}(
			[
				'name'  => 'confirmations_required',
				'value' => $module_data->confirmations_required,
				'type'  => 'number',
				'min'   => 1
			]
		).
		h::{'p button[is=cs-button][type=submit]'}(
			$L->save,
			[
				'tooltip' => $L->save_info
			]
		)
	)
);
