<?php
/**
 * @package  Fotorama
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
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
