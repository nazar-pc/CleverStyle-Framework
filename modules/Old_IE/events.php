<?php
/**
 * @package   Old IE
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
		if (
			strpos(Request::instance()->header('user-agent'), 'MSIE 11') !== false &&
			Config::instance()->module('Old_IE')->enabled()
		) {
			Response::instance()->header('x-ua-compatible', 'IE=edge');
			Page::instance()->Head .=
				h::script(['src' => 'modules/Old_IE/includes/js/a.Promise.min.js']).
				h::script(['src' => 'modules/Old_IE/includes/js/b.Template.js']);
		}
	}
);
