<?php
/**
 * @package   Service worker cache
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'System/Page/display/before',
	function () {
		if (Config::instance()->module('Service_worker_cache')->enabled()) {
			$version = file_get_json(__DIR__.'/meta.json')['version'];
			Page::instance()
				->config($version, 'cs.service_worker_cache.version')
				->js("/components/modules/Service_worker_cache/includes/js/register.js?$version");
		}
	}
);
