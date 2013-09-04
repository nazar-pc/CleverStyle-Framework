<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System\components\blocks;
use			h,
			cs\Cache,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page,
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
				h::{'p.lead.cs-center'}(
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
				h::{'p.lead.cs-center'}(
					$L->adding_a_block
				).
				h::{'table.cs-table-borderless.cs-center-all tr'}(
					\cs\modules\System\form_rows_to_cols([
						array_map(
							function ($in) {
								return h::{'th info'}($in);
							},
							[
								'block_type',
								'block_title',
								'block_active',
								'block_template',
								'block_start',
								'block_expire'
							]
						),
						array_map(
							function ($in) {
								return h::td($in);
							},
							[
								h::select(
									array_merge(['html', 'raw_html'], _mb_substr(get_files_list(BLOCKS, '/^block\..*?\.php$/i', 'f'), 6, -4)),
									[
										'name'		=> 'block[type]',
										'size'		=> 5,
										'onchange'	=> 'cs.block_switch_textarea(this)'
									]
								),
								h::input([
									'name'		=> 'block[title]'
								]),
								h::{'div input[type=radio]'}([
									'name'		=> 'block[active]',
									'value'		=> [1, 0],
									'in'		=> [$L->yes, $L->no]
								]),
								h::select(
									_mb_substr(get_files_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6),
									[
										'name'		=> 'block[template]',
										'size'		=> 5
									]
								),
								h::{'input[type=datetime-local]'}([
									'name'		=> 'block[start]',
									'value'		=> date('Y-m-d\TH:i', TIME)
								]),
								h::{'input[type=radio]'}([
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
						)
					]),
					[
						h::{'td[colspan=6] textarea.EDITOR'}(
							'',
							[
								'name'	=> 'block[html]'
							]
						),
						[
							'id'	=> 'cs-block-content-html'
						]
					],
					[
						h::{'td[colspan=6] textarea'}(
							'',
							[
								'name'	=> 'block[raw_html]'
							]
						),
						[
							'style'	=> 'display: none;',
							'id'	=> 'cs-block-content-raw-html'
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
			$Page->title($L->editing_a_block(get_block_title($rc[3])));
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->editing_a_block(get_block_title($rc[3]))
				).
				h::{'table.cs-table-borderless.cs-center-all tr'}(
					\cs\modules\System\form_rows_to_cols([
						array_map(
							function ($in) {
								return h::{'th info'}($in);
							},
							[
								'block_title',
								'block_active',
								'block_template',
								'block_start',
								'block_expire'
							]
						),
						array_map(
							function ($in) {
								return h::td($in);
							},
							[
								h::input([
									'name'		=> 'block[title]',
									'value'		=> get_block_title($rc[3])
								]),
								h::{'div input[type=radio]'}([
									'name'		=> 'block[active]',
									'checked'	=> $block['active'],
									'value'		=> [1, 0],
									'in'		=> [$L->yes, $L->no]
								]),
								h::select(
									[
										'in'		=> _mb_substr(get_files_list(TEMPLATES.'/blocks', '/^block\..*?\.(php|html)$/i', 'f'), 6)
									],
									[
										'name'		=> 'block[template]',
										'selected'	=> $block['template'],
										'size'		=> 5
									]
								),
								h::{'input[type=datetime-local]'}([
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
								h::{'input[type=datetime-local]'}([
									'name'		=> 'block[expire][date]',
									'value'		=> date('Y-m-d\TH:i', $block['expire'] ?: TIME)
								])
							]
						)
					]),
					($block['type'] == 'html' ? h::{'td[colspan=5] textarea.EDITOR'}(
							get_block_content($rc[3]),
							[
								'name'	=> 'block[html]'
							]
						) : (
							$block['type'] == 'raw_html' ? h::{'td[colspan=5] textarea'}(
								get_block_content($rc[3]),
								[
									'name'	=> 'block[raw_html]'
								]
							) : ''
						)
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
			$permission				= $User->get_permission(null, 'Block', $Config->components['blocks'][$rc[3]]['index'])[0]['id'];
			$groups					= $User->get_groups_list();
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
				$groups_content[] = h::th(
					$group['title'],
					[
						'data-title'	=> $group['description']
					]
				).
				h::{'td input[type=radio]'}([
					'name'			=> "groups[$group[id]]",
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
				$users_content[]	= h::th($User->username($user)).
					h::{'td input[type=radio]'}([
						'name'			=> 'users['.$user.']',
						'checked'		=> $value,
						'value'			=> [-1, 0, 1],
						'in'			=> [$L->inherited, $L->deny, $L->allow]
					]);
			}
			unset($user, $value);
			$Page->title($L->permissions_for_block(get_block_title($rc[3])));
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->permissions_for_block(get_block_title($rc[3]))
				).
				h::{'ul.cs-tabs li'}(
					$L->groups,
					$L->users
				).
				h::div(
					h::{'table.cs-table-borderless.cs-center-all tr'}(
						h::{'td.cs-left-all[colspan=4]'}(
							h::{'button.cs-permissions-invert'}($L->invert).
							h::{'button.cs-permissions-allow-all'}($L->allow_all).
							h::{'button.cs-permissions-deny-all'}($L->deny_all)
						),
						$groups_content
					).
					h::{'table.cs-table-borderless.cs-center-all tr'}([
						h::{'td.cs-left-all'}(
							h::{'button.cs-permissions-invert'}($L->invert).
							h::{'button.cs-permissions-allow-all'}($L->allow_all).
							h::{'button.cs-permissions-deny-all'}($L->deny_all)
						),
						h::{'td table#cs-block-users-changed-permissions.cs-table-borderless.cs-center-all tr'}($users_content),
						h::{'td input#block_users_search[type=search]'}([
							'autocomplete'	=> 'off',
							'permission'	=> $permission,
							'placeholder'	=> $L->type_username_or_email_press_enter,
							'style'			=> 'width: 100%'
						]),
						h::{'td#block_users_search_results'}()
					])
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
		break;
		case 'search_users':
			$form				= false;
			$a->generate_auto	= false;
			interface_off();
			$users_list		= $User->search_users($_POST['search_phrase']);
			$found_users	= explode(',', $_POST['found_users']);
			$permission		= (int)$_POST['permission'];
			$content		= [];
			foreach ($users_list as $user) {
				if (in_array($user, $found_users)) {
					continue;
				}
				$found_users[]	= $user;
				$value			= $User->db()->qfs([
					"SELECT `value`
					FROM `[prefix]users_permissions`
					WHERE
						`id`			= '%s' AND
						`permission`	= '%s'",
					$user,
					$permission
				]);
				$content[]		= h::th($User->username($user)).
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
				h::{'table.cs-table-borderless.cs-center-all tr'}($content)
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
	if (!empty($Config->components['blocks'])) {
		foreach ($Config->components['blocks'] as $id => $block) {
			$blocks_array[$block['position']] .= h::li(
				h::{'div.cs-blocks-items-title'}("#$block[index] ".get_block_title($id)).
				h::a(
					[
						h::{'div icon'}('edit'),
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
						h::{'div icon'}($block['active'] ? 'check-minus' : 'check'),
						[
							'href'			=> "$a->action/".($block['active'] ? 'disable' : 'enable')."/$id",
							'data-title'	=> $L->{$block['active'] ? 'disable' : 'enable'}
						]
					],
					[
						h::{'div icon'}('trash'),
						[
							'href'			=> "$a->action/delete/$id",
							'data-title'	=> $L->delete
						]
					]
				),
				[
					'id'	=> "block$id",
					'class'	=> $block['active'] ? 'uk-button-success' : 'uk-button-default'
				]
			);
			unset($block_data);
		}
		unset($id, $block);
	}
	foreach ($blocks_array as $position => &$content) {
		$content = h::{'td.cs-blocks-items-groups ul.cs-blocks-items'}(
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
		h::{'table.cs-table-borderless tr'}([
			h::td().$blocks_array['top'].h::td(),

			"$blocks_array[left]$blocks_array[floating]$blocks_array[right]",

			h::td().$blocks_array['bottom'].h::td()
		]).
		h::{'p.cs-left a.cs-button'}(
			"$L->add $L->block",
			[
			'href' => "admin/System/$rc[0]/$rc[1]/add"
			]
		).
		h::{'input#cs-blocks-position[type=hidden][name=position]'}()
	);
}