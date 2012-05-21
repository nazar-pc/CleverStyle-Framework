<?php
global $Config, $Index, $L, $Page;
$a		= &$Index;
$rc		= &$Config->routing['current'];
$form	= true;
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'enable':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			$Config->components['blocks'][$rc[3]]['active'] = 1;
			$a->save('components');
		break;
		case 'disable':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			$Config->components['blocks'][$rc[3]]['active'] = 0;
			$a->save('components');
		break;
		case 'delete':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			$form			= false;
			$a->buttons		= false;
			$a->cancel_back	= true;
			$a->action		= 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1];
			$a->content(
				h::{'p.cs-center-all'}(
					$L->sure_to_delete_block($Config->components['blocks'][$rc[3]]['title']).
					h::{'input[type=hidden]'}([
						'name'	=> 'mode',
						'value'	=> 'delete'
					]).
					h::{'input[type=hidden]'}([
						'name'	=> 'id',
						'value'	=> $rc[3]
					])
				).
				h::{'button[type=submit]'}($L->yes)
			);
		break;
		case 'add':
			$form					= false;
			$a->apply				= false;
			$a->cancel_back			= true;
			$a->form_atributes[]	= 'formnovalidate';
			$a->content(
				h::{'table.cs-admin-table.cs-center-all'}(
					h::{'tr th.ui-widget-header.ui-corner-all'}([
						h::info('block_type'),
						h::info('block_title'),
						h::info('block_active'),
						h::info('block_template'),
						h::info('block_start'),
						h::info('block_expire'),
						h::info('block_update')
					]).
					h::{'tr td.ui-widget-content.ui-corner-all.cs-add-block'}([
						h::{'select.cs-form-element'}(
							[
								'in'		=> array_merge(['html', 'raw_html'], _mb_substr(get_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4))
							],
							[
								'name'		=> 'block[type]',
								'size'		=> 5,
								'onchange'	=> 'block_switch_textarea(this)'
							]
						),
						h::{'input.cs-form-element'}([
							'name'		=> 'block[title]'
						]),
						h::{'input[type=radio]'}([
							'name'		=> 'block[active]',
							'value'		=> [1, 0],
							'in'		=> [$L->yes, $L->no]
						]),
						h::{'select.cs-form-element'}(
							[
								'in'		=> _mb_substr(get_list(TEMPLATES.DS.'blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6)
							],
							[
								'name'		=> 'block[template]',
								'size'		=> 5
							]
						),
						h::{'input.cs-form-element[type=datetime-local]'}([
							'name'		=> 'block[start]',
							'value'		=> date('Y-m-d\TH:i', TIME)
						]),
						h::{'input[type=radio]'}([
							'name'		=> 'block[expire][state]',
							'value'		=> [0, 1],
							'in'		=> [$L->never, $L->as_specified]
						]).
						h::br(2).
						h::{'input.cs-form-element[type=datetime-local]'}([
							'name'		=> 'block[expire][date]',
							'value'		=> date('Y-m-d\TH:i', TIME)
						]),
						h::{'input.cs-form-element[type=time]'}([
							'name'		=> 'block[update]',
							'value'		=> '01:00'
						])
					]).
					h::{'tr#html td.ui-widget-content.ui-corner-all[colspan=7] textarea.EDITOR.cs-form-element'}(
						'',
						[
							'name'	=> 'block[html]'
						]
					).
					h::{'tr#raw_html'}(
						h::{'td.ui-widget-content.ui-corner-all[colspan=7] textarea.cs-form-element.cs-wide-textarea'}(
							'',
							[
								'name'	=> 'block[raw_html]'
							]
						),
						[
							'style'	=> 'display: none;'
						]
					)
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'mode',
					'value'	=> $rc[2]
				])
			);
		break;
		case 'edit':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			$form					= false;
			$a->apply				= false;
			$a->cancel_back			= true;
			$a->form_atributes[]	= 'formnovalidate';
			$block = &$Config->components['blocks'][$rc[3]];
			$a->content(
				h::{'table.cs-admin-table.cs-center-all'}(
					h::{'tr th.ui-widget-header.ui-corner-all'}([
						h::info('block_title'),
						h::info('block_active'),
						h::info('block_template'),
						h::info('block_start'),
						h::info('block_expire'),
						h::info('block_update')
					]).
					h::{'tr td.ui-widget-content.ui-corner-all.cs-add-block'}([
						h::{'input.cs-form-element'}([
							'name'		=> 'block[title]',
							'value'		=> $block['title']
						]),
						h::{'input[type=radio]'}([
							'name'		=> 'block[active]',
							'checked'	=> $block['active'],
							'value'		=> [1, 0],
							'in'		=> [$L->yes, $L->no]
						]),
						h::{'select.cs-form-element'}(
							[
								'in'		=> _mb_substr(get_list(TEMPLATES.DS.'blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6)
							],
							[
								'name'		=> 'block[template]',
								'selected'	=> $block['template'],
								'size'		=> 5
							]
						),
						h::{'input.cs-form-element[type=datetime-local]'}([
							'name'		=> 'block[start]',
							'value'		=> date('Y-m-d\TH:i', $block['start'] ?: TIME)
						]),
						h::{'input[type=radio]'}([
							'name'		=> 'block[expire][state]',
							'checked'	=> $block['expire'] != 0,
							'value'		=> [0, 1],
							'in'		=> [$L->never, $L->as_specified]
						]).
						h::br(2).
						h::{'input.cs-form-element[type=datetime-local]'}([
							'name'		=> 'block[expire][date]',
							'value'		=> date('Y-m-d\TH:i', $block['expire'] ?: TIME)
						]),
						h::{'input.cs-form-element[type=time]'}([
							'name'		=> 'block[update]',
							'value'		=> str_pad(round($block['update'] / 60), 2, 0, STR_PAD_LEFT).':'.
								str_pad(round($block['update'] % 60), 2, 0, STR_PAD_LEFT)
						])
					]).
					($block['type'] == 'html' ?
						h::{'tr td.ui-widget-content.ui-corner-all[colspan=6] textarea.EDITOR.cs-form-element'}(
							$block['data'],
							[
								'name'	=> 'block[html]'
							]
						) : ($block['type'] == 'raw_html' ?
						h::{'tr td.ui-widget-content.ui-corner-all[colspan=6] textarea.cs-form-element.cs-wide-textarea'}(
							$block['data'],
							[
								'name'	=> 'block[raw_html]'
							]
						) : '')
					)
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'block[id]',
					'value'	=> $rc[3]
				]).
				h::{'input[type=hidden]'}([
					'name'	=> 'mode',
					'value'	=> $rc[2]
				])
			);
		break;
	}
}
if ($form) {
	$a->reset = false;
	$a->post_buttons .= h::{'button.cs-reload-button'}(
		$L->reset
	);
	$blocks_array = [
		'top'		=> '',
		'left'		=> '',
		'floating'	=> '',
		'right'		=> '',
		'bottom'	=> ''
	];
	foreach ($Config->components['blocks'] as $id => $block) {
		$blocks_array[$block['position']] .= h::li(
			h::{'div.cs-blocks-items-title'}('#'.$block['index'].' '.$block['title']).
			h::a(
				h::{'div icon'}('wrench'),
				[
					'href'			=> $a->action.'/edit/'.$id,
					'data-title'	=> $L->edit.' '.$L->block
				]
			).
			h::a(
				h::{'div icon'}($block['active'] ? 'minusthick' : 'check'),
				[
					'href'			=> $a->action.'/'.($block['active'] ? 'disable' : 'enable').'/'.$id,
					'data-title'	=> $L->{$block['active'] ? 'disable' : 'enable'}
				]
			).
			h::a(
				h::{'div icon'}('trash'),
				[
					'href'			=> $a->action.'/delete/'.$id,
					'data-title'	=> $L->delete
				]
			),
			[
				'id'				=> 'block'.$id,
				'class'				=> ($block['active'] ? 'ui-widget-header' : 'ui-widget-content').' ui-corner-all'
			]
		);
		unset($block_data);
	}
	unset($block);
	foreach ($blocks_array as $position => &$content) {
		$content = h::{'td.cs-blocks-items-groups'}(
			h::{'ul.cs-blocks-items'}(
				h::{'li.ui-state-disabled.ui-state-highlight.ui-corner-all'}(
					$L->{$position.'_blocks'},
					[
						'onClick'	=> 'blocks_toggle(\''.$position.'\');'
					]
				).
				$content,
				[
					'data-mode'		=> 'open',
					'id'			=> $position.'_blocks_items'
				]
			)
		);
	}
	unset($position, $content);
	$a->content(
		h::{'table.cs-admin-table tr'}([
			h::td().$blocks_array['top'].h::td(),

			$blocks_array['left'].$blocks_array['floating'].$blocks_array['right'],

			h::td().$blocks_array['bottom'].h::td(),

			h::{'td.cs-left-all[colspan=3]'}(
				h::button(
					$L->add.' '.$L->block,
					[
						'onMouseDown' => 'javasript: location.href= \'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1].'/add\';'
					]
				)
			)
		]).
		h::{'input#position[type=hidden][name=position]'}()
	);
}