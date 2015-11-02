<?php
/**
 * @package        Comments
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'System/Index/construct',
	function () {
		if (Config::instance()->module('Disqus')->enabled()) {
			require __DIR__.'/events/enabled.php';
		}
	}
);
