<?php
/**
 * @package   Prism
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;

Event::instance()->on(
	'System/Page/render/before',
	function () {
		if (!Config::instance()->module('Prism')->enabled()) {
			return;
		}
		Page::instance()->Head .= h::{'link[rel=stylesheet][shim-shadowdom]'}(['href' => 'modules/Prism/includes/css/final.css']);
	}
);
