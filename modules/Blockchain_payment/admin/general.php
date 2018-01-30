<?php
/**
 * @package  Blockchain payment
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
use
	cs\Config,
	cs\Language\Prefix,
	cs\Page;

$L           = new Prefix('blockchain_payment_');
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
	h::{'cs-form form'}(
		h::{'label info'}('blockchain_payment_bitcoin_address').
		h::{'cs-input-text input'}(
			[
				'name'  => 'bitcoin_address',
				'value' => $module_data->bitcoin_address
			]
		).
		h::{'label info'}('blockchain_payment_confirmations_required').
		h::{'cs-input-text input'}(
			[
				'name'  => 'confirmations_required',
				'value' => $module_data->confirmations_required,
				'type'  => 'number',
				'min'   => 1
			]
		).
		h::{'p cs-button'}(
			h::{'button[type=submit]'}($L->save),
			[
				'tooltip' => $L->save_info
			]
		)
	)
);
