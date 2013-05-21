<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System;
use			h;
global $Config, $Index;
$timezones	= get_timezones_list();
$sa			= $Config->core['simple_admin_mode'];
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		core_input('name', 'text', 'site_name'),
		!$sa ? core_input('url') : false,
		!$sa ? core_input('cookie_domain') : false,
		!$sa ? core_input('cookie_path') : false,
		!$sa ? core_input('cookie_prefix') : false,
		!$sa ? [
			h::info('mirrors'),
			h::{'table tr| td'}(
				[
					h::info('mirrors_url'),
					h::info('mirrors_cookie_domain'),
					h::info('mirrors_cookie_path')
				],
				[
					[
						h::{'textarea.cs-wide-textarea'}(
							$Config->core['mirrors_url'],
							[
							'name' => 'core[mirrors_url]'
							]
						),
						h::{'textarea.cs-wide-textarea'}(
							$Config->core['mirrors_cookie_domain'],
							[
							'name' => 'core[mirrors_cookie_domain]'
							]
						),
						h::{'textarea.cs-wide-textarea'}(
							$Config->core['mirrors_cookie_path'],
							[
							'name' => 'core[mirrors_cookie_path]'
							]
						)
					],
					[
						'id'	=> 'site_info_config_mirrors'
					]
				]
			)
		] : false,
		core_input('keywords'),
		core_input('description'),
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
		core_input('admin_email', 'email'),
		core_input('admin_phone', 'tel')
	)
);