<?php
/**
 * @package   Fotorama
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;

Event::instance()->on(
	'System/Page/render/before',
	function () {
		if (!Config::instance()->module('Fotorama')->enabled()) {
			return;
		}
	}
);
