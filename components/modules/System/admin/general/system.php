<?php
global $L, $Config, $Index;
$a = &$Index;

$a->content(
	h::{'table.admin_table.left_even.right_odd'}(
		h::tr(
			h::td(h::info('site_mode')).
			h::td(
				h::{'input[type=radio]'}(
					array(
						'name'			=> 'core[site_mode]',
						'checked'		=> $Config->core['site_mode'],
						'value'			=> array(0, 1),
						'in'			=> array($L->off, $L->on)
					)
				)
			)
		).
		h::tr(
			h::td(h::info('closed_title')).
			h::td(
				h::{'input.form_element'}(
					array(
						'name'			=> 'core[closed_title]',
						'value'			=> $Config->core['closed_title']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('closed_text')).
			h::td(
				h::{'textarea#closed_text.EDITORH.form_element'}(
					$Config->core['closed_text'],
					array('name' => 'core[closed_text]')
				)
			)
		).
		h::tr(
			h::td(h::info('title_delimiter')).
			h::td(
				h::{'input.form_element'}(
					array(
						'name'			=> 'core[title_delimiter]',
						'value'			=> $Config->core['title_delimiter']
					)
				)
			)
		).
		h::tr(
			h::td(h::info('title_reverse')).
				h::td(
					h::{'input[type=radio]'}(
						array(
							'name'			=> 'core[title_reverse]',
							'checked'		=> $Config->core['title_reverse'],
							'value'			=> array(0, 1),
							'in'			=> array($L->off, $L->on)
						)
					)
				)
		).
		h::tr(
			h::td($L->show_tooltips).
				h::td(
					h::{'input[type=radio]'}(
						array(
							'name'			=> 'core[show_tooltips]',
							'checked'		=> $Config->core['show_tooltips'],
							'value'			=> array(0, 1),
							'in'			=> array($L->off, $L->on)
						)
					)
				)
		).
		h::tr(
			h::td(h::info('debug')).
			h::td(
				h::{'input[type=radio]'}(
					array(
						'name'			=> 'core[debug]',
						'checked'		=> $Config->core['debug'],
						'value'			=> array(0, 1),
						'in'			=> array($L->off, $L->on),
						'OnClick'		=> array('$(\'#debug_form\').hide();', '$(\'#debug_form\').show();')
					)
				)
			)
		).
		h::tr(
			h::td().
			h::{'td#debug_form'}(
				h::table(
					h::tr(
						h::td($L->show_objects_data).
						h::td(
							h::{'input[type=radio]'}(
								array(
									'name'			=> 'core[show_objects_data]',
									'checked'		=> $Config->core['show_objects_data'],
									'value'			=> array(0, 1),
									'in'			=> array($L->off, $L->on)
								)
							)
						)
					).
					h::tr(
						h::td($L->show_user_data).
						h::td(
							h::{'input[type=radio]'}(
								array(
									'name'			=> 'core[show_user_data]',
									'checked'		=> $Config->core['show_user_data'],
									'value'			=> array(0, 1),
									'in'			=> array($L->off, $L->on)
								)
							)
						)
					).
					h::tr(
						h::td($L->show_queries).
						h::td(
							h::{'input[type=radio]'}(
								array(
									'name'			=> 'core[show_queries]',
									'checked'		=> $Config->core['show_queries'],
									'value'			=> array(0, 1),
									'in'			=> array($L->off, $L->on)
								)
							)
						)
					).
					h::tr(
						h::td($L->show_cookies).
						h::td(
							h::{'input[type=radio]'}(
								array(
									'name'			=> 'core[show_cookies]',
									'checked'		=> $Config->core['show_cookies'],
									'value'			=> array(0, 1),
									'in'			=> array($L->off, $L->on)
								)
							)
						)
					)
				),
				array(
					'style' => ($Config->core['debug'] == 0 ? 'display: none;' : '')
				)
			)
		).
		h::tr(
			h::td(h::info('routing')).
			h::td(
				h::{'table#system_config_routing'}(
					h::tr(
						h::td(h::info('routing_in')).
						h::td(h::info('routing_out'))
					).
					h::tr(
						h::td(
							h::{'textarea.form_element'}(
								$Config->routing['in'],
								array(
									'name'			=> 'routing[in]'
								)
							)
						).
						h::td(
							h::{'textarea.form_element'}(
								$Config->routing['out'],
								array(
									'name'			=> 'routing[out]'
								)
							)
						)
					)
				)
			)
		).
		h::tr(
			h::td(h::info('replace')).
			h::td(
				h::{'table#system_config_replace'}(
					h::tr(
						h::td(h::info('replace_in')).
						h::td(h::info('replace_out'))
					).
					h::tr(
						h::td(
							h::{'textarea.form_element'}(
								$Config->replace['in'],
								array(
									'name'			=> 'replace[in]'
								)
							)
						).
						h::td(
							h::{'textarea.form_element'}(
								$Config->replace['out'],
								array(
									'name'			=> 'replace[out]'
								)
							)
						)
					)
				)
			)
		)
	)
);
unset($a);
?>