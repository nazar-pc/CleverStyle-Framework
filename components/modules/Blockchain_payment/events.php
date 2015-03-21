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

Event::instance()->on(
	'System/Index/construct',
	function () {
		if (Config::instance()->module('Blockchain_payment')->active()) {
			require __DIR__.'/events/enabled.php';
		}
	}
);
