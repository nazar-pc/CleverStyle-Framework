<?php
global $Config, $Index, $L, $Page;
$a = &$Index;
$rc = &$Config->routing['current'];
if (isset($rc[2], $rc[3]) && isset($Config->components['blocks'][$rc[3]])) {
	switch ($rc[2]) {
		case 'enable':
			$Config->components['blocks'][$rc[3]]['active'] = 1;
			$a->save('components');
		break;
		case 'disable':
			$Config->components['blocks'][$rc[3]]['active'] = 0;
			$a->save('components');
		break;
		case 'edit':
			$edit					= true;
			$a->apply				= false;
			$a->cancel_back			= true;
			$a->form_atributes[]	= 'formnovalidate';
			$block = &$Config->components['blocks'][$rc[3]];
			$a->content(
				h::{'table.admin_table.center_all'}(
					h::{'tr th.ui-widget-header.ui-corner-all'}(
						[
							h::info('block_title'),
							h::info('block_active'),
							h::info('block_template'),
							h::info('block_start'),
							h::info('block_expire'),
							h::info('block_update')
						]
					).
					h::{'tr td.ui-widget-content.ui-corner-all.block_add'}(
						[
							h::{'input.form_element'}(
								[
									'name'		=> 'block[title]',
									'value'		=> $block['title']
								]
							),
							h::{'input[type=radio]'}(
								[
									'name'		=> 'block[active]',
									'checked'	=> $block['active'],
									'value'		=> array(1, 0),
									'in'		=> array($L->yes, $L->no)
								]
							),
							h::{'select.form_element'}(
								[
									'in'		=> _mb_substr(get_list(TEMPLATES.DS.'blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6)
								],
								[
									'name'		=> 'block[template]',
									'selected'	=> $block['template'],
									'size'		=> 5
								]
							),
							h::{'input.form_element[type=datetime-local]'}(
								[
									'name'		=> 'block[start]',
									'value'		=> date('Y-m-d\TH:i', $block['start'] ?: TIME)
								]
							),
							h::{'input[type=radio]'}(
								[
									'name'		=> 'block[expire][state]',
									'checked'	=> $block['expire'] != 0,
									'value'		=> array(0, 1),
									'in'		=> array($L->never, $L->as_specified)
								]
							).
							h::br(2).
							h::{'input.form_element[type=datetime-local]'}(
								[
									'name'		=> 'block[expire][date]',
									'value'		=> date('Y-m-d\TH:i', $block['expire'] ?: TIME)
								]
							),
							h::{'input.form_element[type=time]'}(
								[
									'name'		=> 'block[update]',
									'value'		=> str_pad(round($block['update'] / 60), 2, 0, STR_PAD_LEFT).':'.
										str_pad(round($block['update'] % 60), 2, 0, STR_PAD_LEFT)
								]
							)
						]
					)
				).
				h::{'input[type=hidden]'}(
					array(
						'name'	=> 'block[id]',
						'value'	=> $rc[3]
					)
				).
				h::{'input[type=hidden]'}(
					array(
						'name'	=> 'mode',
						'value'	=> $rc[2]
					)
				)
			);
		break;
	}
}
if (!isset($edit)) {
	$a->savecross = true;
	$a->reset = false;
	$a->post_buttons .= h::{'button.reload_button'}(
		$L->reset
	);
	$blocks_array = [
		'top'		=> [],
		'left'		=> [],
		'floating'	=> [],
		'right'		=> [],
		'bottom'	=> []
	];
	$blocks = _mb_substr(get_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4);
	$diff = array_diff(array_keys($Config->components['blocks']), $blocks);
	$save = false;
	if (!empty($diff)) {
		//Deleting of old blocks
		foreach ($diff as $key) {
			unset($Config->components['blocks'][$key], $key);
		}
		//Save changes
		$save = true;
	}
	unset($diff, $key);
	$num = 999;
	foreach ($blocks as $block) {
		//If block was not found in db
		if (!isset($Config->components['blocks'][$block])) {
			$Config->components['blocks'][$block] = [
				'title'			=> $block,
				'active'		=> 0,
				'position'		=> 'floating',
				'position_id'	=> $num++,
				'template'		=> 'default.html',
				'permissions'	=> '',
				'start'			=> TIME,
				'expire'		=> 0,
				'update'		=> 0,
				'data'			=> ''
			];
			//Save changes
			$save = true;
		}
		$block_data = &$Config->components['blocks'][$block];
		$blocks_array[$block_data['position']][$block_data['position_id']] = h::li(
			h::{'div.blocks_items_title'}($block_data['title']).
			h::a(
				h::{'div icon'}('wrench'),
				[
					'href'			=> $a->action.'/settings/'.$block,
					'data-title'	=> $L->edit.' '.$L->block
				]
			).
			h::a(
				h::{'div icon'}($block_data['active'] ? 'minusthick' : 'check'),
				[
					'href'			=> $a->action.'/'.($block_data['active'] ? 'disable' : 'enable').'/'.$block,
					'data-title'	=> $L->{$block_data['active'] ? 'disable' : 'enable'}
				]
			),
			[
				'id'				=> $block,
				'class'				=> ($block_data['active'] ? 'ui-widget-header' : 'ui-widget-content').' ui-corner-all'
			]
		);
		unset($block_data);
	}
	$save && $a->save('components');
	unset($blocks, $block, $save, $num);
	foreach ($blocks_array as $position => &$content) {
		ksort($content);
		$content = h::{'td.blocks_items_groups'}(
			h::{'ul.blocks_items'}(
				h::{'li.ui-state-disabled.ui-state-highlight.ui-corner-all'}(
					$L->{$position.'_blocks'},
					[
						'onClick'	=> 'blocks_toggle(\''.$position.'\');'
					]
				).
				implode('', $content),
				[
					'data-mode'		=> 'open',
					'id'			=> $position.'_blocks_items'
				]
			)
		);
	}
	unset($position, $content);
	$a->content(
		h::{'table.admin_table tr'}(
			[
				h::td().$blocks_array['top'].h::td(),

				$blocks_array['left'].$blocks_array['floating'].$blocks_array['right'],

				h::td().$blocks_array['bottom'].h::td()
			]
		).
		h::{'input#position[type=hidden][name=position]'}()
	);
}
//TODO make add block function