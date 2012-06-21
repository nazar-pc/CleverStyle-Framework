<?php
global $Config, $Index, $L;
$a = &$Index;

$a->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd'}(
		h::tr(
			h::td(h::info('smtp')).
			h::td(
				h::{'input[type=radio]'}(
					array(
						'name'			=> 'core[smtp]',
						'checked'		=> $Config->core['smtp'],
						'value'			=> array(0, 1),
						'in'			=> array($L->off, $L->on),
						'OnClick'		=> array('$(\'#smtp_form\').hide();', '$(\'#smtp_form\').show();')
					)
				)
			)
		).
		h::tr(
			h::td().
			h::td(
				h::{'table#smtp_form'}(
					h::tr(
						h::td(h::info('smtp_host')).
						h::td(
							h::{'input.cs-form-element'}(
								array(
									'name'	=> 'core[smtp_host]',
									'value' => $Config->core['smtp_host']
								)
							)
						)
					).
					h::tr(
						h::td(h::info('smtp_port')).
						h::td(
							h::{'input.cs-form-element'}(
								array(
									'name'	=> 'core[smtp_port]',
									'value' => $Config->core['smtp_port']
								)
							)
						)
					).
					h::tr(
						h::td(h::info('smtp_secure')).
						h::td(
							h::{'input[type=radio]'}(
								array(
									'name'			=> 'core[smtp_secure]',
									'checked'		=> $Config->core['smtp_secure'],
									'value'			=> array('', 'ssl', 'tls'),
									'in'			=> array($L->off, 'SSL', 'TLS')
								)
							)
						)
					).
					h::tr(
						h::td($L->smtp_auth).
						h::td(
							h::{'input[type=radio]'}(
								array(
									'name'			=> 'core[smtp_auth]',
									'checked'		=> $Config->core['smtp_auth'],
									'value'			=> array(0, 1),
									'in'			=> array($L->off, $L->on),
									'OnClick'		=> array('$(\'#smtp_user, #smtp_password\').hide();', '$(\'#smtp_user, #smtp_password\').show();')
								)
							)
						)
					).
					h::{'tr#smtp_user'}(
						h::td($L->smtp_user).
						h::td(
							h::{'input.cs-form-element'}(
								array(
									'name'	=> 'core[smtp_user]',
									'value' => $Config->core['smtp_user']
								)
							)
						),
						array(
							'style' => ($Config->core['smtp_auth'] == 0 ? 'display: none; ' : '').'padding-left: 20px;'
						)
					).
					h::{'tr#smtp_password'}(
						h::td(h::info('smtp_password')).
						h::td(
							h::{'input.cs-form-element'}(
								array(
									'name'	=> 'core[smtp_password]',
									'value' => $Config->core['smtp_password']
								)
							)
						),
						array('style' => $Config->core['smtp_auth'] == 0 ? 'display: none; ' : '')
					)
				),
				array(
					'style' => ($Config->core['smtp'] == 0 ? 'display: none; ' : '')
				)
			)
		).
		h::tr(
			h::td(h::info('mail_from')).
			h::td(
				h::{'input.cs-form-element'}(
					array(
						'name'	=> 'core[mail_from]',
						'value' => $Config->core['mail_from']
					)
				)
			)
		).
		h::tr(
			h::td($L->mail_from_name).
				h::td(
					h::{'input.cs-form-element'}(
						array(
							 'name'	=> 'core[mail_from_name]',
							 'value' => $Config->core['mail_from_name']
						)
					)
				)
		).
		h::tr(
			h::td(h::info('mail_signature')).
				h::td(
					h::{'textarea.EDITORH.cs-form-element'}(
						$Config->core['mail_signature'],
						array('name' => 'core[mail_signature]')
					)
				)
		)
	)
);
unset($a);
?>