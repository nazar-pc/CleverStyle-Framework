<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2013
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'admin/System/components/modules/install/process',
	function ($data) {
		if ($data['name'] != 'OAuth2') {
			return;
		}
		Config::instance()->module('OAuth2')->set([
			'guest_tokens'				=> 0,
			'expiration'				=> 3600,
			'automatic_prolongation'	=> 1
		]);
		return;
	}
);