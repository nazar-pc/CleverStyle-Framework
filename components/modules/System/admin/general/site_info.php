<?php
global $Config, $Index, $L;
$timezones	= get_timezones_list();
$sa			= $Config->core['simple_admin_mode'];
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		system_input_core('name', 'text', 'site_name'),
		!$sa ? system_input_core('url') : false,
		!$sa ? system_input_core('cookie_domain') : false,
		!$sa ? system_input_core('cookie_path') : false,
		!$sa ? system_input_core('cookie_prefix') : false,
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
						h::{'textarea.cs-form-element.cs-wide-textarea'}(
							$Config->core['mirrors_url'],
							[
							'name' => 'core[mirrors_url]'
							]
						),
						h::{'textarea.cs-form-element.cs-wide-textarea'}(
							$Config->core['mirrors_cookie_domain'],
							[
							'name' => 'core[mirrors_cookie_domain]'
							]
						),
						h::{'textarea.cs-form-element.cs-wide-textarea'}(
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
		system_input_core('keywords'),
		system_input_core('description'),
		[
			h::info('timezone'),
			h::{'select.cs-form-element'}(
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
		system_input_core('admin_email', 'email'),
		system_input_core('admin_phone', 'tel')
	)
);