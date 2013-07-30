<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Index/mainmenu',
	function ($data) {
		if ($data['path'] == 'Feedback') {
			$data['path']	= path(Language::instance()->Feedback);
		}
	}
);