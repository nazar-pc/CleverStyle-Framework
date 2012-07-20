<?php
namespace	cs\modules\System;
use			\h;
global $L, $Config, $Index;
$sa	= $Config->core['simple_admin_mode'];
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		system_input_core('site_mode', 'radio'),
		system_input_core('closed_title'),
		system_textarea_core('closed_text', false, 'EDITORH'),
		system_input_core('title_delimiter'),
		system_input_core('title_reverse', 'radio'),
		system_textarea_core('footer_text', false, 'EDITORH'),
		system_input_core('show_footer_info', 'radio'),
		system_input_core('show_tooltips', 'radio'),
		system_input_core('simple_admin_mode', 'radio'),
		!$sa ? [
			h::info('debug'),
				h::{'input[type=radio]'}([
				'name'			=> 'core[debug]',
				'checked'		=> $Config->core['debug'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on],
				'OnClick'		=> ['$(\'#debug_form\').hide();', '$(\'#debug_form\').show();']
			])
		] : false,
		!$sa ? [
			'',
			[
				h::{'table tr| td'}(
					system_input_core('show_objects_data', 'radio'),
					system_input_core('show_db_queries', 'radio'),
					system_input_core('show_cookies', 'radio')
				),
				[
					'style' => ($Config->core['debug'] == 0 ? 'display: none;' : ''),
					'id'	=> 'debug_form',
					'class'	=> 'cs-padding-left'
				]
			]
		] : false,
		!$sa ? [
			h::info('routing'),
			h::{'table#system_config_routing.cs-fullwidth-table tr| td'}(
				[
					h::info('routing_in'),
					h::info('routing_out')
				],
				[
					h::{'textarea.cs-form-element.cs-wide-textarea'}(
						$Config->routing['in'],
						[
							'name'				=> 'routing[in]'
						]
					),
					h::{'textarea.cs-form-element.cs-wide-textarea'}(
						$Config->routing['out'],
						[
							'name'				=> 'routing[out]'
						]
					)
				]
			)
		] : false,
		!$sa ? [
			h::info('replace'),
			h::{'table#system_config_replace.cs-fullwidth-table tr| td'}(
				[
					h::info('replace_in'),
					h::info('replace_out')
				],
				[
					h::{'textarea.cs-form-element.cs-wide-textarea'}(
						$Config->replace['in'],
						[
							'name'			=> 'replace[in]'
						]
					),
					h::{'textarea.cs-form-element.cs-wide-textarea'}(
						$Config->replace['out'],
						[
							'name'			=> 'replace[out]'
						]
					)
				]
			)
		] : false
	)
);