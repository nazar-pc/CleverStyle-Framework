<?php
/**
 * @package        Admin default theme
 * @category       plugins
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'System/Page/pre_display',
	function () {
		if (ADMIN && in_array('Admin_default_theme', Config::instance()->components['plugins'])) {
			Page::instance()->set_theme('CleverStyle')->set_color_scheme('Default');
		}
	}
);