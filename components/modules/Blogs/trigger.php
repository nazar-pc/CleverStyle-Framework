<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Core;
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($data) {
		global $L, $Config;
		if (!$Config->module('Blogs')->active() && substr($data['rc'], 0, 5) != 'admin') {
			return;
		}
		$rc		= explode('/', $data['rc']);
		if ($rc[0] == $L->Blogs || $rc[0] == 'Blogs') {
			$rc[0]		= 'Blogs';
			$data['rc']	= implode('/', $rc);
		}
	}
);
$Core->register_trigger(
	'System/Index/construct',
	function () {
		global $Config;
		switch ($Config->components['modules']['Blogs']['active']) {
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