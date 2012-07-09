<?php
global $Config, $Index, $L, $Page;
$a		= $Index;
$rc		= $Config->routing['current'];
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
			global $Cache;
			unset($Cache->{'blocks/'.$Config->components['blocks'][$rc[3]]['index']});
		break;
		case 'delete':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			$form					= false;
			$a->buttons				= false;
			$a->cancel_button_back	= true;
			$a->action				= 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1];
			$Page->title($L->deleting_a_block($Config->components['blocks'][$rc[3]]['title']));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
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
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$a->form_atributes[]	= 'formnovalidate';
			$Page->title($L->adding_a_block);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->adding_a_block
				).
				h::{'table.cs-fullwidth-table.cs-center-all tr'}(
					h::{'th.ui-widget-header.ui-corner-all| info'}(
						'block_type',
						'block_title',
						'block_active',
						'block_template',
						'block_start',
						'block_expire',
						'block_update'
					),
					h::{'td.ui-widget-content.ui-corner-all.cs-add-block'}(
						h::{'select.cs-form-element'}(
							array_merge(['html', 'raw_html'], _mb_substr(get_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4)),
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
							_mb_substr(get_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6),
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
					),
					[
						h::{'td.ui-widget-content.ui-corner-all[colspan=7] textarea.EDITOR.cs-form-element'}(
							'',
							[
								'name'	=> 'block[html]'
							]
						),
						[
							'id'	=> 'block_content_html'
						]
					],
					[
						h::{'td.ui-widget-content.ui-corner-all[colspan=7] textarea.cs-form-element.cs-wide-textarea'}(
							'',
							[
								'name'	=> 'block[raw_html]'
							]
						),
						[
							'style'	=> 'display: none;',
							'id'	=> 'block_content_raw_html'
						]
					]
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
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$a->form_atributes[]	= 'formnovalidate';
			$block = &$Config->components['blocks'][$rc[3]];
			$Page->title($L->editing_a_block($block['title']));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->editing_a_block($block['title'])
				).
				h::{'table.cs-fullwidth-table.cs-center-all tr'}(
					h::{'th.ui-widget-header.ui-corner-all| info'}(
						'block_title',
						'block_active',
						'block_template',
						'block_start',
						'block_expire',
						'block_update'
					),
					h::{'td.ui-widget-content.ui-corner-all.cs-add-block'}(
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
								'in'		=> _mb_substr(get_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6)
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
							'value'		=> str_pad(round($block['update'] / 3600), 2, 0, STR_PAD_LEFT).':'.
								str_pad(round($block['update'] % 3600), 2, 0, STR_PAD_LEFT)
						])
					),
					($block['type'] == 'html' ? h::{'td.ui-widget-content.ui-corner-all[colspan=6] textarea.EDITOR.cs-form-element'}(
							$block['data'],
							[
								'name'	=> 'block[html]'
							]
						) : (
							$block['type'] == 'raw_html' ? h::{'td.ui-widget-content.ui-corner-all[colspan=6] textarea.cs-form-element.cs-wide-textarea'}(
								$block['data'],
								[
									'name'	=> 'block[raw_html]'
								]
							) : ''
						)
					)
				).
				h::{'input[type=hidden]'}([
					[
						'name'	=> 'block[id]',
						'value'	=> $rc[3]
					],
					[
						'name'	=> 'mode',
						'value'	=> $rc[2]
					]
				])
			);
		break;
		case 'permissions':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			global $User;
			$form					= false;
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$block					= $Config->components['blocks'][$rc[3]];
			$permission				= $User->get_permission(null, 'Block', $block['index'])[0]['id'];
			$groups					= $User->get_groups_list();
			$groups_content			= [];
			foreach ($groups as $group) {
				$group_permission = $User->db()->qf(
					[
						"SELECT `value` FROM `[prefix]groups_permissions` WHERE `id` = '%s' AND `permission` = '%s'",
						$group['id'],
						$permission
					],
					'value'
				);
				$groups_content[] = h::{'th.ui-widget-header.ui-corner-all'}(
					$group['title'],
					[
						'data-title'	=> $group['description']
					]
				).
				h::{'td input[type=radio]'}([
					'name'			=> 'groups['.$group['id'].']',
					'checked'		=> $group_permission === false ? -1 : $group_permission,
					'value'			=> [-1, 0, 1],
					'in'			=> [$L->inherited, $L->deny, $L->allow]
				]);
			}
			unset($groups, $group, $group_permission);
			if (count($groups_content) % 2) {
				$groups_content[] = h::{'td[colspan=2]'}();
			}
			$count			= count($groups_content);
			$content_		= [];
			for ($i = 0; $i < $count; $i += 2) {
				$content_[]	= $groups_content[$i].$groups_content[$i+1];
			}
			$groups_content	= $content_;
			unset($count, $content_);
			$users_list		= $User->db()->qfa([
				"SELECT `id`, `value` FROM `[prefix]users_permissions` WHERE `permission` = '%s'",
				$permission
			]);
			$users_content	= [];
			foreach ($users_list as &$user) {
				$value				= $user['value'];
				$user				= $user['id'];
				$users_content[]	= h::{'th.ui-widget-header.ui-corner-all'}($User->get_username($user)).
					h::{'td input[type=radio]'}([
						'name'			=> 'users['.$user.']',
						'checked'		=> $value,
						'value'			=> [-1, 0, 1],
						'in'			=> [$L->inherited, $L->deny, $L->allow]
					]);
			}
			unset($user, $value);
			$Page->title($L->permissions_for_block($block['title']));
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->permissions_for_block($block['title'])
				).
				h::{'div#block_permissions_tabs'}(
					h::{'ul li| a'}(
						[
							$L->groups,
							[
								'href'	=> '#block_groups_permissions'
							]
						],
						[
							$L->users,
							[
								'href'	=> '#block_users_permissions'
							]
						]
					).
					h::{'div#block_groups_permissions table.cs-fullwidth-table.cs-center-all tr'}(
						h::{'td.cs-left-all[colspan=4]'}(
							h::{'button.cs-permissions-invert'}($L->invert).
							h::{'button.cs-permissions-allow-all'}($L->allow_all).
							h::{'button.cs-permissions-deny-all'}($L->deny_all)
						),
						$groups_content
					).
					h::{'input#block_users_search_found[type=hidden]'}([
						'value'	=> implode(',', $users_list)
					]).
					h::{'div#block_users_permissions table.cs-fullwidth-table.cs-center-all tr'}([
						h::{'td.cs-left-all'}(
							h::{'button.cs-permissions-invert'}($L->invert).
							h::{'button.cs-permissions-allow-all'}($L->allow_all).
							h::{'button.cs-permissions-deny-all'}($L->deny_all)
						),
						h::{'td table#block_users_changed_permissions.cs-fullwidth-table.cs-center-all tr'}($users_content),
						h::{'td input#block_users_search.cs-form-element[type=search]'}([
							'autocomplete'	=> 'off',
							'permission'	=> $permission,
							'placeholder'	=> $L->type_username_or_email_press_enter,
							'style'			=> 'width: 100%'
						]),
						h::{'td#block_users_search_results'}()
					])
				).
				h::br().
				h::{'input[type=hidden]'}([
					[
						'name'	=> 'block[id]',
						'value'	=> $rc[3]
					],
					[
						'name'	=> 'mode',
						'value'	=> $rc[2]
					]
				])
			);
		break;
		case 'search_users':
			$form				= false;
			$a->generate_auto	= false;
			interface_off();
			global $User;
			$users_list		= $User->search_users($_POST['search_phrase']);
			$found_users	= explode(',', $_POST['found_users']);
			$permission		= (int)$_POST['permission'];
			$content		= [];
			foreach ($users_list as $user) {
				if (in_array($user, $found_users)) {
					continue;
				}
				$found_users[]	= $user;
				$value			= $User->db()->qf(
					[
						"SELECT `value` FROM `[prefix]users_permissions` WHERE `id` = '%s' AND `permission` = '%s'",
						$user,
						$permission
					],
					'value'
				);
				$content[]		= h::{'th.ui-widget-header.ui-corner-all'}($User->get_username($user)).
					h::{'td input[type=radio]'}([
						'name'			=> 'users['.$user.']',
						'checked'		=> $value !== false ? $value : -1,
						'value'			=> [-1, 0, 1],
						'in'			=> [
							$L->inherited.' ('.($value !== false && !$value ? '-' : '+').')',
							$L->deny,
							$L->allow
						]
					]);
			}
			$Page->content(
				h::{'table.cs-fullwidth-table.cs-center-all tr'}($content)
			);
		break;
	}
}
if ($form) {
	$a->reset_button	= false;
	$a->post_buttons	.= h::{'button.cs-reload-button'}(
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
			h::{'a'}(
				[
					h::{'div icon'}('wrench'),
					[
						'href'			=> $a->action.'/edit/'.$id,
						'data-title'	=> $L->edit
					]
				],
				[
					h::{'div icon'}('flag'),
					[
						'href'			=> $a->action.'/permissions/'.$id,
						'data-title'	=> $L->edit_permissions
					]
				],
				[
					h::{'div icon'}($block['active'] ? 'minusthick' : 'check'),
					[
						'href'			=> $a->action.'/'.($block['active'] ? 'disable' : 'enable').'/'.$id,
						'data-title'	=> $L->{$block['active'] ? 'disable' : 'enable'}
					]
				],
				[
					h::{'div icon'}('trash'),
					[
						'href'			=> $a->action.'/delete/'.$id,
						'data-title'	=> $L->delete
					]
				]
			),
			[
				'id'	=> 'block'.$id,
				'class'	=> ($block['active'] ? 'ui-widget-header' : 'ui-widget-content').' ui-corner-all'
			]
		);
		unset($block_data);
	}
	unset($block);
	foreach ($blocks_array as $position => &$content) {
		$content = h::{'td.cs-blocks-items-groups ul.cs-blocks-items'}(
			h::{'li.ui-state-disabled.ui-state-highlight.ui-corner-all'}(
				$L->{$position.'_blocks'},
				[
					'onClick'	=> 'blocks_toggle(\''.$position.'\');'
				]
			).
			$content,
			[
				'data-mode'	=> 'open',
				'id'		=> $position.'_blocks_items'
			]
		);
	}
	unset($position, $content);
	$a->content(
		h::{'table.cs-fullwidth-table tr'}([
			h::td().$blocks_array['top'].h::td(),

			$blocks_array['left'].$blocks_array['floating'].$blocks_array['right'],

			h::td().$blocks_array['bottom'].h::td(),

			h::{'td.cs-left-all[colspan=3] button'}(
				$L->add.' '.$L->block,
				[
					'onMouseDown' => 'javasript: location.href= \'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1].'/add\';'
				]
			)
		]).
		h::{'input#position[type=hidden][name=position]'}()
	);
}