<?php
global $Config, $Index, $L;
$a = &$Index;
$timezones = get_timezones_list();

$a->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr'}([
		h::td([
			h::info('name2'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[name]',
				'value' => $Config->core['name']
			])
		]),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('url'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[url]',
				'value' => $Config->core['url']
			])
		]) : false),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('cookie_domain'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[cookie_domain]',
				'value' => $Config->core['cookie_domain']
			])
		]) : false),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('cookie_path'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[cookie_path]',
				'value' => $Config->core['cookie_path']
			])
		]) : false),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('cookie_prefix'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[cookie_prefix]',
				'value' => $Config->core['cookie_prefix']
			])
		]) : false),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('mirrors'),
			h::table(
				h::{'tr td'}([
					h::info('mirrors_url'),
					h::info('mirrors_cookie_domain'),
					h::info('mirrors_cookie_path')
				]).
				h::{'tr#site_info_config_mirrors td'}([
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
				])
			)
		]) : false),

		h::td([
			h::info('keywords'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[keywords]',
				'value' => $Config->core['keywords']
			])
		]),

		h::td([
			h::info('description'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[description]',
				'value' => $Config->core['description']
			])
		]),

		h::td([
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
		]),

		h::td([
			h::info('admin_email'),
			h::{'input.cs-form-element[type=email]'}([
				'name'	=> 'core[admin_email]',
				'value' => $Config->core['admin_email']
			])
		]),

		h::td([
			h::info('admin_phone'),
			h::{'input.cs-form-element[type=tel]'}([
				'name'	=> 'core[admin_phone]',
				'value' => $Config->core['admin_phone']
			])
		])
	])
);