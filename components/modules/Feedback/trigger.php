<?php
/**
 * @package		Feedback
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Config/routing_replace',
	function ($data) {
		if (!Config::instance()->module('Feedback')->active() && substr($data['rc'], 0, 5) != 'admin') {
			return;
		}
		$rc		= explode('/', $data['rc']);
		if ($rc[0] == path(Language::instance()->Feedback) || $rc[0] == 'Feedback') {
			$rc[0]		= 'Feedback';
			$data['rc']	= implode('/', $rc);
		}
	}
)->register(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Feedback']['active']) {
			case 1:
				require __DIR__.'/trigger/enabled.php';
		}
	}
);