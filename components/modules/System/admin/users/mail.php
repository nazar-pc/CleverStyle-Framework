<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System;
use			h,
			cs\Config,
			cs\Index,
			cs\Language;
$Config	= Config::instance();
$L		= Language::instance();
Index::instance()->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}([
		[
			h::info('smtp'),
			h::radio([
				'name'			=> 'core[smtp]',
				'checked'		=> $Config->core['smtp'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on],
				'OnClick'		=> ["$('#smtp_form').parent().parent().hide();", "$('#smtp_form').parent().parent().show();"]
			])
		],
		[
			[
				'',
				h::{'table#smtp_form tr'}(
					h::td(
						core_input('smtp_host')
					),
					h::td(
						core_input('smtp_port')
					),
					h::td([
						h::info('smtp_secure'),
						h::radio([
							'name'			=> 'core[smtp_secure]',
							'checked'		=> $Config->core['smtp_secure'],
							'value'			=> ['', 'ssl', 'tls'],
							'in'			=> [$L->off, 'SSL', 'TLS']
						])
					]),
					h::td([
						$L->smtp_auth,
						h::radio([
							'name'			=> 'core[smtp_auth]',
							'checked'		=> $Config->core['smtp_auth'],
							'value'			=> [0, 1],
							'in'			=> [$L->off, $L->on],
							'OnClick'		=> ["$('#smtp_user, #smtp_password').hide();", "$('#smtp_user, #smtp_password').show();"]
						])
					]),
					[
						h::td(
							core_input('smtp_user')
						),
						[
							'style' => (!$Config->core['smtp_auth'] ? 'display: none;' : '').' padding-left: 20px;',
							'id'	=> 'smtp_user'
						]
					],
					[
						h::td(
							core_input('smtp_password')
						),
						[
							'style' => !$Config->core['smtp_auth'] ? 'display: none;' : '',
							'id'	=> 'smtp_password'
						]
					]
				)
			],
			[
				'style' => !$Config->core['smtp'] ? 'display: none; ' : ''
			]
		],
		core_input('mail_from'),
		core_input('mail_from_name'),
		core_textarea('mail_signature', 'SIMPLE_EDITOR'),
		[
			'',
			h::{'td button.uk-button[onclick=cs.test_email_sending()]'}($L->test_email_sending)
		]
	])
);
