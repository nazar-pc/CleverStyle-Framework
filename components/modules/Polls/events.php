<?php
/**
 * @package   Polls
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

Event::instance()->on(
	'System/Index/construct',
	function () {
		if (Config::instance()->module('Polls')->installed()) {
			require __DIR__.'/events/installed.php';
		}
	}
);
