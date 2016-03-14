<?php
/**
 * @package   Old IE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;

Event::instance()->on(
	'System/Page/display/before',
	function () {
		if (
			strpos(Request::instance()->user_agent, 'MSIE 10') !== false &&
			in_array('Old_IE', Config::instance()->components['plugins'])
		) {
			Page::instance()->Head .=
				h::{'link[rel=stylesheet][shim-shadowdom]'}(['href' => 'components/plugins/Old_IE/includes/css/hidden.css']).
				h::script(['src' => 'components/plugins/Old_IE/includes/js/a.WeakMap.js']).
				h::script(['src' => 'components/plugins/Old_IE/includes/js/b.MutationObserver.js']);
		}
	}
);
