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
$Config		= Config::instance();
$timezones	= get_timezones_list();
$sa			= $Config->core['simple_admin_mode'];
Index::instance()->content(
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr| td'}(
		core_input('name', 'text', 'site_name'),
		!$sa ? core_textarea('url') : false,
		!$sa ? core_textarea('cookie_domain') : false,
		!$sa ? core_textarea('cookie_path') : false,
		!$sa ? core_input('cookie_prefix') : false,
		[
			h::info('timezone'),
			h::select(
				[
					'in'		=> array_keys($timezones),
					'value'		=> array_values($timezones)
				],
				[
					'name'		=> 'core[timezone]',
					'selected'	=> $Config->core['timezone'],
					'size'		=> 7
				]
			)
		],
		core_input('admin_email', 'email')
	)
);
