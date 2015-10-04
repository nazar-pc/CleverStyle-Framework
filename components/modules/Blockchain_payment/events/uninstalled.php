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
	cs\Event;

Event::instance()->on(
	'admin/System/components/modules/install/after',
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
