<?php
global $Config, $Index, $L, $User;
$a = &$Index;

$a->content(
	h::{'table.cs-admin-table.cs-left-even.cs-right-odd tr'}([
		h::td([
			h::info('key_expire'),
			h::{'input.cs-form-element[type=number]'}([
				'name'			=> 'core[key_expire]',
				'value'			=> $Config->core['key_expire'],
				'min'			=> 1
			]).
			$L->seconds
		]),

		h::td([
			h::info('ip_black_list'),
			h::{'textarea.cs-form-element.cs-wide-textarea'}(
				$Config->core['ip_black_list'],
				[
					'name' => 'core[ip_black_list]'
				]
			)
		]),

		h::td([
			h::info('ip_admin_list_only'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[ip_admin_list_only]',
				'checked'		=> $Config->core['ip_admin_list_only'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		]),

		h::td([
			h::info('ip_admin_list'),
			h::{'textarea.cs-form-element.cs-wide-textarea'}(
				$Config->core['ip_admin_list'],
				[
					'name' => 'core[ip_admin_list]'
				]
			).
			h::br().
			$L->current_ip.': '.h::b($User->ip)
		]),

		h::td([
			h::info('on_error_globals_dump'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[on_error_globals_dump]',
				'checked'		=> $Config->core['on_error_globals_dump'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		])
	])
);//TODO logs reader