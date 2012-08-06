<?php
/**
 * @package        Static Pages
 * @category       modules
 * @version        0.001
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
global $Core;
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($rc) {
		$module	= basename(__DIR__);
		//TODO routing processing with cache
	}
);