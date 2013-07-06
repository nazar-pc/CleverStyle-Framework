<?php
/**
 * @package        Admin default theme
 * @category       plugins
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
global $Core;
$Core->register_trigger(
	'System/Page/pre_display',
	function () {
		global $Page, $Config;
		if (ADMIN && in_array('Admin_default_theme', $Config->components['plugins'])) {
			$Page->set_theme('CleverStyle');
			$Page->set_color_scheme('Green');
		}
	}
);