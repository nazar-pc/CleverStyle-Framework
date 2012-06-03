<?php
global $Config, $Index, $L;
$a = &$Index;
$timezones = get_timezones_list();

$a->content(
	h::{'table.cs-admin-table.cs-left-even.cs-right-odd tr'}([
		h::td([
			h::info('name2'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[name]',
				'value' => $Config->core['name']
			])
		]),

		h::td([
			h::info('url'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[url]',
				'value' => $Config->core['url']
			])
		]),

		h::td([
			h::info('cookie_domain'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[cookie_domain]',
				'value' => $Config->core['cookie_domain']
			])
		]),

		h::td([
			h::info('cookie_path'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[cookie_path]',
				'value' => $Config->core['cookie_path']
			])
		]),

		h::td([
			h::info('cookie_prefix'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[cookie_prefix]',
				'value' => $Config->core['cookie_prefix']
			])
		]),

		h::td([
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
		]),

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
					'in'		=> array_values($timezones),
					'value'		=> array_keys($timezones)
				],
				[
					'name'		=> 'core[timezone]',
					'selected'	=> $Config->core['timezone'],
					'size'		=> 7
				]
			)
		]),

		h::td([
			h::info('admin_mail'),
			h::{'input.cs-form-element[type=email]'}([
				'name'	=> 'core[admin_mail]',
				'value' => $Config->core['admin_mail']
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