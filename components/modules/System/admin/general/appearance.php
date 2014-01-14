<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System;
use			h,
			cs\Config,
			cs\Index;
$Config	= Config::instance();
$Config->reload_themes();
Index::instance()->content(
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr| td'}(
		core_select($Config->core['themes'],							'theme',			'change_theme',			'current_theme'),
		core_select($Config->core['color_schemes'][$Config->core['theme']],	'color_scheme',		'change_color_scheme')
	)
);
