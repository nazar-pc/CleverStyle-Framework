<?php
global $Config, $Index, $L;
$a = &$Index;

$a->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr'}([
		h::td([
			h::info('smtp'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[smtp]',
				'checked'		=> $Config->core['smtp'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on],
				'OnClick'		=> ['$(\'#smtp_form\').hide();', '$(\'#smtp_form\').show();']
			])
		]),
		h::td(
			[
				'',
				h::{'table#smtp_form'}(
					h::tr([
						h::td([
							h::info('smtp_host'),
							h::{'input.cs-form-element'}([
								'name'	=> 'core[smtp_host]',
								'value' => $Config->core['smtp_host']
							])
						]),
						h::td([
							h::info('smtp_port'),
							h::{'input.cs-form-element'}([
								'name'	=> 'core[smtp_port]',
								'value' => $Config->core['smtp_port']
							])
						]),
						h::td([
							h::info('smtp_secure'),
							h::{'input[type=radio]'}([
								'name'			=> 'core[smtp_secure]',
								'checked'		=> $Config->core['smtp_secure'],
								'value'			=> ['', 'ssl', 'tls'],
								'in'			=> [$L->off, 'SSL', 'TLS']
							])
						]),
						h::td([
							$L->smtp_auth,
							h::{'input[type=radio]'}([
								'name'			=> 'core[smtp_auth]',
								'checked'		=> $Config->core['smtp_auth'],
								'value'			=> [0, 1],
								'in'			=> [$L->off, $L->on],
								'OnClick'		=> ['$(\'#smtp_user, #smtp_password\').hide();', '$(\'#smtp_user, #smtp_password\').show();']
							])
						])
					]).
					h::{'tr#smtp_user'}(
						h::td([
							$L->smtp_user,
							h::{'input.cs-form-element'}([
								'name'	=> 'core[smtp_user]',
								'value' => $Config->core['smtp_user']
							])
						]),
						[
							'style' => (!$Config->core['smtp_auth'] ? 'display: none; ' : '').'padding-left: 20px;'
						]
					).
					h::{'tr#smtp_password'}(
						h::td([
							h::info('smtp_password'),
							h::{'input.cs-form-element'}([
								'name'	=> 'core[smtp_password]',
								'value' => $Config->core['smtp_password']
							])
						]),
						['style' => !$Config->core['smtp_auth'] ? 'display: none; ' : '']
					)
				)
			],
			[
			'style' => !$Config->core['smtp'] ? 'display: none; ' : ''
			]
		),
		h::td([
			h::info('mail_from'),
			h::{'input.cs-form-element'}([
				'name'	=> 'core[mail_from]',
				'value' => $Config->core['mail_from']
			])
		]),
		h::td([
			$L->mail_from_name,
			h::{'input.cs-form-element'}([
				'name'	=> 'core[mail_from_name]',
				'value' => $Config->core['mail_from_name']
			])
		]),
		h::td([
			h::info('mail_signature'),
			h::{'textarea.EDITORH.cs-form-element'}(
				$Config->core['mail_signature'],
				[
					'name' => 'core[mail_signature]'
				]
			)
		])
	])
);