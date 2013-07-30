<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Config/routing_replace',
	function ($data) {
		if (!Config::instance()->module('Blogs')->active() && substr($data['rc'], 0, 5) != 'admin') {
			return;
		}
		$rc		= explode('/', $data['rc']);
		if ($rc[0] == path(Language::instance()->Blogs) || $rc[0] == 'Blogs') {
			$rc[0]		= 'Blogs';
			$data['rc']	= implode('/', $rc);
		}
	}
)->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Blogs']['active']) {
			case -1:
				if (!ADMIN) {
					return;
				}
				require __DIR__.'/trigger/uninstalled.php';
			break;
			case 1:
				require __DIR__.'/trigger/enabled.php';
			default:
				if (!ADMIN) {
					return;
				}
				require __DIR__.'/trigger/installed.php';
		}
	}
);