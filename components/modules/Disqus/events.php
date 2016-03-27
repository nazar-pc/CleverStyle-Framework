<?php
/**
 * @package        Comments
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'System/App/construct',
	function () {
		if (Config::instance()->module('Disqus')->enabled()) {
			require __DIR__.'/events/enabled.php';
		}
	}
);
