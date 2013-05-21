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
global $Config, $Index, $L;
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		core_input('session_expire', 'number', null, false, 1, false, $L->seconds),
		core_input('online_time', 'number', null, false, 1, false, $L->seconds),
		[
			h::info('login_attempts_block_count'),
			h::{'input[type=number]'}([
				'name'		=> 'core[login_attempts_block_count]',
				'value'		=> $Config->core['login_attempts_block_count'],
				'min'		=> 0,
				'onClick'	=> "if ($(this).val() == 0) { $('.cs-login-attempts-block-count').hide(); } else { $('.cs-login-attempts-block-count').show(); }",
				'onChange'	=> "if ($(this).val() == 0) { $('.cs-login-attempts-block-count').hide(); } else { $('.cs-login-attempts-block-count').show(); }"
			])
		],
		[
			core_input('login_attempts_block_time', 'number', null, false, 1, false, $L->seconds),
			[
				'style'	=> $Config->core['login_attempts_block_count'] == 0 ? 'display: none;' : '',
				'class'	=> 'cs-login-attempts-block-count'
			]
		],
		core_input('remember_user_ip', 'radio'),
		core_input('password_min_length', 'number', null, false, 4),
		core_input('password_min_strength', 'range', null, false, 0, 7),
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
			core_input('registration_confirmation_time', 'number', null, false, 1, false, $L->days),
			[
				'style'	=>	$Config->core['allow_user_registration'] == 1 && $Config->core['require_registration_confirmation'] == 1 ? '' : 'display: none;',
				'class'	=> 'cs-allow-user-registration cs-require-registration-confirmation'
			]
		],
		[
			core_input('autologin_after_registration', 'radio'),
			[
				'style'	=>	$Config->core['allow_user_registration'] == 1 && $Config->core['require_registration_confirmation'] == 1 ? '' : 'display: none;',
				'class'	=> 'cs-allow-user-registration cs-require-registration-confirmation'
			]
		],
		core_textarea('rules', 'EDITORH')
	)
);