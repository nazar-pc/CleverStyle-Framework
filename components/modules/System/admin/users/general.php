<?php
global $Config, $Index, $L;
$a = &$Index;

$a->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd'}(
		h::{'tr td'}([
			h::info('session_expire'),
			h::{'input.cs-form-element[type=number]'}([
				'name'			=> 'core[session_expire]',
				'value'			=> $Config->core['session_expire'],
				'min'			=> 1
			]).
			$L->seconds
		]).
		h::{'tr td'}([
			h::info('online_time'),
			h::{'input.cs-form-element[type=number]'}([
				'name'			=> 'core[online_time]',
				'value'			=> $Config->core['online_time'],
				'min'			=> 1
			]).
			$L->seconds
		]).
		h::{'tr td'}([
			h::info('login_attempts_block_count'),
			h::{'input.cs-form-element[type=number]'}([
				'name'			=> 'core[login_attempts_block_count]',
				'value'			=> $Config->core['login_attempts_block_count'],
				'min'			=> 0,
				'onClick'		=> 'if ($(this).val() == 0) {'.
										'$(\'#login_attempts_block_count\').hide();'.
									'} else {'.
										'$(\'#login_attempts_block_count\').show();'.
									'}',
				'onChange'		=> 'if ($(this).val() == 0) {'.
									 	'$(\'#login_attempts_block_count\').hide();'.
									 '} else {'.
									 	'$(\'#login_attempts_block_count\').show();'.
									 '}'
			])
		]).
		h::{'tr#login_attempts_block_count'}(
			h::td(h::info('login_attempts_block_time')).
			h::td(
				h::{'input.cs-form-element[type=number]'}([
					'name'			=> 'core[login_attempts_block_time]',
					'value'			=> $Config->core['login_attempts_block_time'],
					'min'			=> 1
				]).
				$L->seconds
			),
			[
				 'style'	=> $Config->core['login_attempts_block_count'] == 0 ? 'display: none;' : ''
			]
		).
		h::{'tr td'}([
			h::info('remember_user_ip'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[remember_user_ip]',
				'checked'		=> $Config->core['remember_user_ip'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		]).
		h::{'tr td'}([
			h::info('password_min_length'),
			h::{'input.cs-form-element[type=number]'}([
				'name'			=> 'core[password_min_length]',
				'value'			=> $Config->core['password_min_length'],
				'min'			=> 4
			])
		]).
		h::{'tr td'}([
			h::info('password_min_strength'),
			h::{'input.cs-form-element[type=range]'}([
				'name'			=> 'core[password_min_strength]',
				'value'			=> $Config->core['password_min_strength'],
				'min'			=> 0,
				'max'			=> 7
			])
		]).
		h::{'tr td'}([
			h::info('allow_user_registration'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[allow_user_registration]',
				'checked'		=> $Config->core['allow_user_registration'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on],
				'onClick'		=> [
					'$(\'.allow_user_registration\').hide();',
					'$(\'.allow_user_registration\').show();'.
					'if (!$(\'#require_registration_confirmation input[value=1]\').prop(\'checked\')) {'.
						'$(\'.require_registration_confirmation\').hide();'.
					'}'
				]
			])
		]).
		h::{'tr.allow_user_registration'}(
			h::td(h::info('require_registration_confirmation')).
			h::{'td#require_registration_confirmation'}(
				h::{'input[type=radio]'}([
					'name'			=> 'core[require_registration_confirmation]',
					'checked'		=> $Config->core['require_registration_confirmation'],
					'value'			=> [0, 1],
					'in'			=> [$L->off, $L->on],
					'onClick'		=> [
						'$(\'.require_registration_confirmation\').hide();',
						'$(\'.require_registration_confirmation\').show();'
					]
				])
			),
			[
				 'style'	=> $Config->core['allow_user_registration'] == 0 ? 'display: none;' : ''
			]
		).
		h::{'tr.allow_user_registration.require_registration_confirmation'}(
			h::td(h::info('registration_confirmation_time')).
			h::td(
				h::{'input.cs-form-element[type=number]'}([
					 'name'			=> 'core[registration_confirmation_time]',
					 'value'		=> $Config->core['registration_confirmation_time'],
					 'min'			=> 1
				]).
				$L->days
			),
			[
				 'style'	=>	$Config->core['allow_user_registration'] == 0 ||
					 			$Config->core['require_registration_confirmation'] == 1 ? '' : 'display: none;'
			]
		).
		h::{'tr.allow_user_registration.require_registration_confirmation'}(
			h::td(h::info('autologin_after_registration')).
			h::td(
				h::{'input[type=radio]'}([
					'name'			=> 'core[autologin_after_registration]',
					'checked'		=> $Config->core['autologin_after_registration'],
					'value'			=> [0, 1],
					'in'			=> [$L->off, $L->on]
				])
			),
			[
				 'style'	=>	$Config->core['allow_user_registration'] == 0 ||
					 			$Config->core['require_registration_confirmation'] == 1 ? '' : 'display: none;'
			]
		).
		h::{'tr td'}([
			$L->site_rules,
			h::{'textarea#site_rules.EDITORH.cs-form-element'}(
				$Config->core['rules'],
				[
					'name' => 'core[rules]'
				]
			)
		])
	)
);