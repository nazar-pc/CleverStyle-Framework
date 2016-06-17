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
			strpos(Request::instance()->header('user-agent'), 'MSIE 10') !== false &&
			Config::instance()->module('Old_IE')->enabled()
		) {
			Page::instance()->Head .=
				h::{'link[rel=stylesheet][shim-shadowdom]'}(['href' => 'components/modules/Old_IE/includes/css/normalize.css']).
				h::script(['src' => 'components/modules/Old_IE/includes/js/a.WeakMap.js']).
				h::script(['src' => 'components/modules/Old_IE/includes/js/b.MutationObserver.js']);
				h::script(['src' => 'components/modules/Old_IE/includes/js/c.dataset.js']);
		}
	}
);
