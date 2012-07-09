<?php
global $Config, $Index, $L;

$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		system_input_core('session_expire', 'number', null, false, 1, false, $L->seconds),
		system_input_core('online_time', 'number', null, false, 1, false, $L->seconds),
		[
			h::info('login_attempts_block_count'),
			h::{'input.cs-form-element[type=number]'}([
				'name'		=> 'core[login_attempts_block_count]',
				'value'		=> $Config->core['login_attempts_block_count'],
				'min'		=> 0,
				'onClick'	=> "if ($(this).val() == 0) { $('.cs-login-attempts-block-count').hide(); } else { $('.cs-login-attempts-block-count').show(); }",
				'onChange'	=> "if ($(this).val() == 0) { $('.cs-login-attempts-block-count').hide(); } else { $('.cs-login-attempts-block-count').show(); }"
			])
		],
		[
			system_input_core('login_attempts_block_time', 'number', null, false, 1, false, $L->seconds),
			[
				'style'	=> $Config->core['login_attempts_block_count'] == 0 ? 'display: none;' : '',
				'class'	=> 'cs-login-attempts-block-count'
			]
		],
		system_input_core('remember_user_ip', 'radio'),
		system_input_core('password_min_length', 'number', null, false, 4),
		system_input_core('password_min_strength', 'range', null, false, 0, 7),
		[
			h::info('allow_user_registration'),
			h::{'input[type=radio]'}([
				'name'		=> 'core[allow_user_registration]',
				'checked'	=> $Config->core['allow_user_registration'],
				'value'		=> [0, 1],
				'in'		=> [$L->off, $L->on],
				'onClick'	=> [
					"$('.cs-allow-user-registration').hide();",
					"$('.cs-allow-user-registration').show();".
						"if (!$('.cs-allow-user-registration input[value=1]').prop('checked')) { $('.cs-require-registration-confirmation').hide(); }"
				]
			])
		],
		[
			[
				h::info('require_registration_confirmation'),
				h::{'input[type=radio]'}([
					'name'			=> 'core[require_registration_confirmation]',
					'checked'		=> $Config->core['require_registration_confirmation'],
					'value'			=> [0, 1],
					'in'			=> [$L->off, $L->on],
					'onClick'		=> [
						"$('.cs-require-registration-confirmation').hide();",
						"$('.cs-require-registration-confirmation').show();"
					]
				])
			],
			[
				'style'	=> $Config->core['allow_user_registration'] == 0 ? 'display: none;' : '',
				'class'	=> 'cs-allow-user-registration'
			]
		],
		[
			system_input_core('registration_confirmation_time', 'number', null, false, 1, false, $L->days),
			[
				'style'	=>	$Config->core['allow_user_registration'] == 1 && $Config->core['require_registration_confirmation'] == 1 ? '' : 'display: none;',
				'class'	=> 'cs-allow-user-registration cs-require-registration-confirmation'
			]
		],
		[
			system_input_core('autologin_after_registration', 'radio'),
			[
				'style'	=>	$Config->core['allow_user_registration'] == 1 && $Config->core['require_registration_confirmation'] == 1 ? '' : 'display: none;',
				'class'	=> 'cs-allow-user-registration cs-require-registration-confirmation'
			]
		],
		system_textarea_core('rules', false, 'EDITORH')
	)
);