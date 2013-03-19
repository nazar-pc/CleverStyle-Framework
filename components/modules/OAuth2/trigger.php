<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Core;
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($data) {
		global $Config;
		$module	= basename(__DIR__);
		if (!$Config->module($module)->active() && !ADMIN) {
			return;
		}
		global $Core;
		require_once __DIR__.'/OAuth2.php';
		$Core->create('_cs\\modules\\OAuth2\\OAuth2');
		$rc		= explode('/', $data['rc']);
		if (isset($rc[0]) && $rc[0] == $module) {
			$data['rc']	= $rc[0];
		}
	}
);