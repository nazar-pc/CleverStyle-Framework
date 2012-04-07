<?php
global $Config, $Index, $L;
$a = &$Index;
$timezones = get_timezones_list();

$a->content(
	h::{'table.admin_table.left_even.right_odd'}(
		h::tr(
			h::td(h::info('name2')).
			h::td(
				h::{'input.form_element'}(
					array(
						'name'	=> 'core[name]',
						'value' => $Config->core['name']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('url')).
			h::td(
				h::{'input.form_element'}(
					array(
						'name'	=> 'core[url]',
						'value' => $Config->core['url']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('cookie_domain')).
			h::td(
				h::{'input.form_element'}(
					array(
						'name'	=> 'core[cookie_domain]',
						'value' => $Config->core['cookie_domain']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('cookie_path')).
			h::td(
				h::{'input.form_element'}(
					array(
						'name'	=> 'core[cookie_path]',
						'value' => $Config->core['cookie_path']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('cookie_prefix')).
			h::td(
				h::{'input.form_element'}(
					array(
						'name'	=> 'core[cookie_prefix]',
						'value' => $Config->core['cookie_prefix']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('mirrors')).
			h::td(
				h::table(
					h::tr(
						h::td(
							array(
								h::info('mirrors_url'),
								h::info('mirrors_cookie_domain'),
								h::info('mirrors_cookie_path')
							)
						)
					).
					h::{'tr#site_info_config_mirrors'}(
						h::td(
							array(
								h::{'textarea.form_element'}(
									$Config->core['mirrors_url'],
									array('name' => 'core[mirrors_url]')
								),
								h::{'textarea.form_element'}(
									$Config->core['mirrors_cookie_domain'],
									array('name' => 'core[mirrors_cookie_domain]')
								),
								h::{'textarea.form_element'}(
									$Config->core['mirrors_cookie_path'],
									array('name' => 'core[mirrors_cookie_path]')
								)
							)
						)
					)
				)
			)
		).
		h::tr(
			h::td(h::info('keywords')).
			h::td(
				h::{'input.form_element'}(
					array(
						'name'	=> 'core[keywords]',
						'value' => $Config->core['keywords']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('description')).
			h::td(
				h::{'input.form_element'}(
					array(
						'name'	=> 'core[description]',
						'value' => $Config->core['description']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('timezone')).
			h::td(
				h::{'select.form_element'}(
					array(
						'in'		=> array_values($timezones),
						'value'		=> array_keys($timezones)
					),
					array(
						'name'		=> 'core[timezone]',
						'selected'	=> $Config->core['timezone'],
						'size'		=> 7
					)
				)
			)
		).
		h::tr(
			h::td(h::info('admin_mail')).
			h::td(
				h::{'input.form_element[type=email]'}(
					array(
						'name'	=> 'core[admin_mail]',
						'value' => $Config->core['admin_mail']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('admin_phone')).
			h::td(
				h::{'input.form_element[type=tel]'}(
					array(
						'name'	=> 'core[admin_phone]',
						'value' => $Config->core['admin_phone']
					)
				)
			)
		)
	)
);
unset($a);
?>