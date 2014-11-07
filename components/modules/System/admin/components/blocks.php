<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System\components\blocks;
use			h,
			cs\Cache,
			cs\Config,
			cs\Group,
			cs\Index,
			cs\Language,
			cs\Page,
			cs\Permission,
			cs\Text,
			cs\User;
function get_block_title ($id) {
	$Config	= Config::instance();
	return Text::instance()->process($Config->module('System')->db('texts'), $Config->components['blocks'][$id]['title']);
}
function get_block_content ($id) {
	$Config	= Config::instance();
	return Text::instance()->process($Config->module('System')->db('texts'), $Config->components['blocks'][$id]['content']);
}
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$User	= User::instance();
$a		= Index::instance();
$rc		= $Config->route;
$form	= true;
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'enable':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			$Config->components['blocks'][$rc[3]]['active'] = 1;
			$a->save();
		break;
		case 'disable':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			$Config->components['blocks'][$rc[3]]['active'] = 0;
			$a->save();
			unset(Cache::instance()->{'blocks/'.$Config->components['blocks'][$rc[3]]['index']});
		break;
		case 'delete':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			$form					= false;
			$a->buttons				= false;
			$a->cancel_button_back	= true;
			$a->action				= 'admin/System/'.$rc[0].'/'.$rc[1];
			$Page->title($L->deletion_of_block(get_block_title($rc[3])));
			$a->content(
				h::{'h2.cs-center'}(
					$L->sure_to_delete_block(get_block_title($rc[3])).
					h::{'input[type=hidden]'}([
						'name'	=> 'mode',
						'value'	=> 'delete'
					]).
					h::{'input[type=hidden]'}([
						'name'	=> 'id',
						'value'	=> $rc[3]
					])
				).
				h::{'button.uk-button[type=submit]'}($L->yes)
			);
		break;
		case 'add':
			$form					= false;
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$a->form_attributes[]	= 'formnovalidate';
			$Page->title($L->adding_a_block);
			$a->content(
				h::{'h2.cs-center'}(
					$L->adding_a_block
				).
				h::{'cs-table[center][right-left] cs-table-row| cs-table-cell'}(
					[
						h::info('block_type'),
						h::select(
							array_merge(['html', 'raw_html'], _mb_substr(get_files_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4)),
							[
								'name'		=> 'block[type]',
								'size'		=> 5,
								'onchange'	=> 'cs.block_switch_textarea(this)'
							]
						)
					],
					[
						h::info('block_title'),
						h::input([
							'name'		=> 'block[title]'
						])
					],
					[
						h::info('block_active'),
						h::{'div radio'}([
							'name'		=> 'block[active]',
							'value'		=> [1, 0],
							'in'		=> [$L->yes, $L->no]
						])
					],
					[
						h::info('block_template'),
						h::select(
							_mb_substr(get_files_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6),
							[
								'name'		=> 'block[template]',
								'size'		=> 5
							]
						)
					],
					[
						h::info('block_start'),
						h::{'input[type=datetime-local]'}([
							'name'		=> 'block[start]',
							'value'		=> date('Y-m-d\TH:i', TIME)
						])
					],
					[
						h::info('block_expire'),
						h::radio([
							'name'		=> 'block[expire][state]',
							'value'		=> [0, 1],
							'in'		=> [$L->never, $L->as_specified]
						]).
						h::br(2).
						h::{'input[type=datetime-local]'}([
							'name'		=> 'block[expire][date]',
							'value'		=> date('Y-m-d\TH:i', TIME)
						])
					]
				).
				h::{'div#cs-block-content-html textarea.EDITOR'}(
					'',
					[
						'name'	=> 'block[html]'
					]
				).
				h::{'div#cs-block-content-raw-html[style=display:none;] textarea'}(
					'',
					[
						'name'	=> 'block[raw_html]'
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
			$a->form_attributes[]	= 'formnovalidate';
			$block = &$Config->components['blocks'][$rc[3]];
			$Page->title($L->editing_a_block(get_block_title($rc[3])));
			$a->content(
				h::{'h2.cs-center'}(
					$L->editing_a_block(get_block_title($rc[3]))
				).
				h::{'cs-table[center][right-left] cs-table-row| cs-table-cell'}(
					[
						h::info('block_title'),
						h::input([
							'name'		=> 'block[title]',
							'value'		=> get_block_title($rc[3])
						])
					],
					[
						h::info('block_active'),
						h::{'div radio'}([
							'name'		=> 'block[active]',
							'checked'	=> $block['active'],
							'value'		=> [1, 0],
							'in'		=> [$L->yes, $L->no]
						])
					],
					[
						h::info('block_template'),
						h::select(
							[
								'in'		=> _mb_substr(get_files_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6)
							],
							[
								'name'		=> 'block[template]',
								'selected'	=> $block['template'],
								'size'		=> 5
							]
						)
					],
					[
						h::info('block_start'),
						h::{'input[type=datetime-local]'}([
							'name'		=> 'block[start]',
							'value'		=> date('Y-m-d\TH:i', $block['start'] ?: TIME)
						])
					],
					[
						h::info('block_expire'),
						h::radio([
							'name'		=> 'block[expire][state]',
							'checked'	=> $block['expire'] != 0,
							'value'		=> [0, 1],
							'in'		=> [$L->never, $L->as_specified]
						]).
						h::br(2).
						h::{'input[type=datetime-local]'}([
							'name'		=> 'block[expire][date]',
							'value'		=> date('Y-m-d\TH:i', $block['expire'] ?: TIME)
						])
					]
				).
				(
					$block['type'] == 'html'
						? h::{'textarea.EDITOR'}(
							get_block_content($rc[3]),
							[
								'name'	=> 'block[html]'
							]
						)
						: (
							$block['type'] == 'raw_html' ? h::textarea(
								get_block_content($rc[3]),
								[
									'name'	=> 'block[raw_html]'
								]
							) : ''
						)
				).
				h::{'input[type=hidden]'}([
					[[
						'name'	=> 'block[id]',
						'value'	=> $rc[3]
					]],
					[[
						'name'	=> 'mode',
						'value'	=> $rc[2]
					]]
				])
			);
		break;
		case 'permissions':
			if (!isset($rc[3], $Config->components['blocks'][$rc[3]])) {
				break;
			}
			$form					= false;
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$permission				= Permission::instance()->get(null, 'Block', $Config->components['blocks'][$rc[3]]['index'])[0]['id'];
			$groups					= Group::instance()->get_all();
			$groups_content			= [];
			foreach ($groups as $group) {
				$group_permission = $User->db()->qfs([
					"SELECT `value`
					FROM `[prefix]groups_permissions`
					WHERE
						`id`			= '%s' AND
						`permission`	= '%s'",
					$group['id'],
					$permission
				]);
				$groups_content[] = h::cs_table_cell(
					[
						$group['title'],
						[
							'data-title'	=> $group['description']
						]
					],
					h::radio([
						'name'			=> "groups[$group[id]]",
						'checked'		=> $group_permission === false ? -1 : $group_permission,
						'value'			=> [-1, 0, 1],
						'in'			=> [$L->inherited, $L->deny, $L->allow]
					])
				);
			}
			unset($groups, $group, $group_permission);
			if (count($groups_content) % 2) {
				$groups_content[] = h::cs_table_cell().h::cs_table_cell();
			}
			$count			= count($groups_content);
			$content_		= [];
			for ($i = 0; $i < $count; $i += 2) {
				$content_[]	= $groups_content[$i].$groups_content[$i+1];
			}
			$groups_content	= $content_;
			unset($count, $content_);
			$users_list		= $User->db()->qfa([
				"SELECT
					`id`,
					`value`
				FROM `[prefix]users_permissions`
				WHERE `permission` = '%s'",
				$permission
			]);
			$users_content	= [];
			foreach ($users_list as &$user) {
				$value				= $user['value'];
				$user				= $user['id'];
				$users_content[]	= h::cs_table_cell(
					$User->username($user),
					h::radio([
						'name'			=> 'users['.$user.']',
						'checked'		=> $value,
						'value'			=> [-1, 0, 1],
						'in'			=> [$L->inherited, $L->deny, $L->allow]
					])
				);
			}
			unset($user, $value);
			$Page->title($L->permissions_for_block(get_block_title($rc[3])));
			$a->content(
				h::{'h2.cs-center'}(
					$L->permissions_for_block(get_block_title($rc[3]))
				).
				h::{'ul.cs-tabs li'}(
					$L->groups,
					$L->users
				).
				h::{'div div'}(
					h::{'p.cs-left'}(
						h::{'button.uk-button.cs-permissions-invert'}($L->invert).
						h::{'button.uk-button.cs-permissions-allow-all'}($L->allow_all).
						h::{'button.uk-button.cs-permissions-deny-all'}($L->deny_all)
					).
					h::{'cs-table[right-left] cs-table-row'}($groups_content),
					h::{'p.cs-left'}(
						h::{'button.uk-button.cs-permissions-invert'}($L->invert).
						h::{'button.uk-button.cs-permissions-allow-all'}($L->allow_all).
						h::{'button.uk-button.cs-permissions-deny-all'}($L->deny_all)
					).
					h::{'cs-table#cs-block-users-changed-permissions[right-left] cs-table-row'}($users_content).
					h::{'input#block_users_search[type=search]'}([
						'autocomplete'	=> 'off',
						'permission'	=> $permission,
						'placeholder'	=> $L->type_username_or_email_press_enter,
						'style'			=> 'width: 100%'
					]).
					h::{'div#block_users_search_results'}()
				).
				h::{'input#cs-block-users-search-found[type=hidden]'}([
					'value'	=> implode(',', $users_list)
				]).
				h::br().
				h::{'input[type=hidden]'}([
					[[
						'name'	=> 'block[id]',
						'value'	=> $rc[3]
					]],
					[[
						'name'	=> 'mode',
						'value'	=> $rc[2]
					]]
				])
			);
	}
}
if ($form) {
	$a->reset_button	= false;
	$a->custom_buttons	.= h::{'button.uk-button.cs-reload-button'}(
		$L->reset
	);
	$blocks_array = [
		'top'		=> '',
		'left'		=> '',
		'floating'	=> '',
		'right'		=> '',
		'bottom'	=> ''
	];
	if (!empty($Config->components['blocks'])) {
		foreach ($Config->components['blocks'] as $id => $block) {
			$blocks_array[$block['position']] .= h::li(
				h::{'div.cs-blocks-items-title'}("#$block[index] ".get_block_title($id)).
				h::a(
					[
						h::{'div icon'}('pencil'),
						[
							'href'			=> "$a->action/edit/$id",
							'data-title'	=> $L->edit
						]
					],
					[
						h::{'div icon'}('key'),
						[
							'href'			=> "$a->action/permissions/$id",
							'data-title'	=> $L->edit_permissions
						]
					],
					[
						h::{'div icon'}($block['active'] ? 'minus' : 'check'),
						[
							'href'			=> "$a->action/".($block['active'] ? 'disable' : 'enable')."/$id",
							'data-title'	=> $L->{$block['active'] ? 'disable' : 'enable'}
						]
					],
					[
						h::{'div icon'}('trash-o'),
						[
							'href'			=> "$a->action/delete/$id",
							'data-title'	=> $L->delete
						]
					]
				),
				[
					'data-id'	=> $id,
					'class'		=> $block['active'] ? 'uk-button-success' : 'uk-button-default'
				]
			);
			unset($block_data);
		}
		unset($id, $block);
	}
	foreach ($blocks_array as $position => &$content) {
		$content = h::{'cs-table-cell.cs-blocks-items-groups ul.cs-blocks-items'}(
			h::{'li.uk-button-primary'}(
				$L->{"{$position}_blocks"},
				[
					'onClick'	=> "cs.blocks_toggle('$position');"
				]
			).
			$content,
			[
				'data-mode'	=> 'open',
				'id'		=> "cs-{$position}-blocks-items"
			]
		);
	}
	unset($position, $content);
	$a->content(
		h::{'cs-table cs-table-row'}([
			h::cs_table_cell().$blocks_array['top'].h::cs_table_cell(),

			"$blocks_array[left]$blocks_array[floating]$blocks_array[right]",

			h::cs_table_cell().$blocks_array['bottom'].h::cs_table_cell()
		]).
		h::{'p.cs-left a.uk-button'}(
			"$L->add $L->block",
			[
			'href' => "admin/System/$rc[0]/$rc[1]/add"
			]
		).
		h::{'input#cs-blocks-position[type=hidden][name=position]'}()
	);
}
