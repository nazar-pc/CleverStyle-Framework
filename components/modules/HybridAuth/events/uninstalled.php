<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2012-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace	cs;
Event::instance()->on(
	'admin/System/components/modules/install/after',
	function ($data) {
		if ($data['name'] != 'HybridAuth') {
			return;
		}
		Config::instance()->module('HybridAuth')->providers	= [];
	}
);
