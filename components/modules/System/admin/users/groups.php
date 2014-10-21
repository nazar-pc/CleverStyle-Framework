<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$Group	= Group::instance();
$a		= Index::instance();
$rc		= $Config->route;
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'add':
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$Page->title($L->adding_a_group);
			$a->content(
				h::{'h2.cs-center'}(
					$L->adding_a_group
				).
				h::{'cs-table[center][with-header] cs-table-row| cs-table-cell'}(
					[
						$L->group_name,
						$L->group_description
					],
					[
						h::{'input[name=group[title]]'}(),
						h::{'input[name=group[description]]'}()
					]
				)
			);
		break;
		case 'edit':
			if (!isset($rc[3])) {
				break;
			}
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$group_data				= $Group->get($rc[3]);
			$Page->title(
				$L->editing_of_group($group_data['title'])
			);
			$a->content(
				h::{'h2.cs-center'}(
					$L->editing_of_group($group_data['title'])
				).
				h::{'cs-table[center][with-header] cs-table-row| cs-table-cell'}(
					[
						'&nbsp;id&nbsp;',
						$L->group_name,
						$L->group_description,
						'data'
					],
					[
						$rc[3],
						h::input([
							'name'		=> 'group[title]',
							'value'		=> $group_data['title']
						]),
						h::input([
							'name'		=> 'group[description]',
							'value'		=> $group_data['description']
						]),
						h::{'textarea[name=group[data]]'}(
							$group_data['data']
						)
					]
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'group[id]',
					'value'	=> $rc[3]
				])
			);
		break;
		case 'delete':
			if (!isset($rc[3])) {
				break;
			}
			$a->buttons				= false;
			$a->cancel_button_back	= true;
			$group					= $Group->get($rc[3]);
			$Page->title(
				$L->deletion_of_group($group['title'])
			);
			$a->content(
				h::{'h2.cs-center'}(
					$L->sure_delete_group($group['title'])
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'id',
					'value'	=> $rc[3]
				]).
				h::{'button.uk-button[type=submit]'}($L->yes)
			);
			$Page->warning($L->changing_settings_warning);
		break;
		case 'permissions':
			if (!isset($rc[3])) {
				break;
			}
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$permissions			= Permission::instance()->get_all();
			$group_permissions		= $Group->get_permissions($rc[3]);
			$tabs					= [];
			$tabs_content			= '';
			$blocks					= [];
			foreach ($Config->components['blocks'] as $block) {
				$blocks[$block['index']] = $block['title'];
			}
			unset($block);
			foreach ($permissions as $group => $list) {
				$tabs[]		= h::a(
					$group,
					[
						'href'	=> '#permissions_group_'.strtr($group, '/', '_')
					]
				);
				$content	= [];
				foreach($list as $label => $id) {
					$content[] = h::cs_table_cell(
						$group == 'Block' ? Text::instance()->process($Config->module('System')->db('texts'), $blocks[$label]) : $label,
						h::radio([
							'name'			=> "permission[$id]",
							'checked'		=> isset($group_permissions[$id]) ? $group_permissions[$id] : -1,
							'value'			=> [-1, 0, 1],
							'in'			=> [$L->not_specified, $L->deny, $L->allow]
						])
					);
				}
				if (count($list) % 2) {
					$content[] = h::cs_table_cell().h::cs_table_cell();
				}
				$count		= count($content);
				$content_	= [];
				for ($i = 0; $i < $count; $i += 2) {
					$content_[]	= $content[$i].$content[$i+1];
				}
				$tabs_content .= h::{'div#permissions_group_'.strtr($group, '/', '_')}(
					h::{'p.cs-left'}(
						h::{'button.uk-button.cs-permissions-invert'}($L->invert).
						h::{'button.uk-button.cs-permissions-deny-all'}($L->deny_all).
						h::{'button.uk-button.cs-permissions-allow-all'}($L->allow_all)
					).
					h::{'cs-table[right-left] cs-table-row'}($content_)
				);
			}
			unset($content, $content_, $count, $i, $permissions, $group, $list, $label, $id, $blocks);
			$Page->title(
				$L->permissions_for_group(
					$Group->get($rc[3], 'title')
				)
			);
			$a->content(
				h::{'h2.cs-center'}(
					$L->permissions_for_group(
						$Group->get($rc[3], 'title')
					)
				).
				h::{'ul.cs-tabs li'}($tabs).
				h::div($tabs_content).
				h::br().
				h::{'input[type=hidden]'}([
					'name'	=> 'id',
					'value'	=> $rc[3]
				])
			);
		break;
	}
	$a->content(
		h::{'input[type=hidden]'}([
			'name'	=> 'mode',
			'value'	=> $rc[2]
		])
	);
} else {
	$a->buttons		= false;
	$groups_ids		= $Group->get_all();
	$groups_list	= [];
	foreach ($groups_ids as $id) {
		$id				= $id['id'];
		$group_data 	= $Group->get($id);
		$groups_list[]	= [
			h::{'a.uk-button.cs-button-compact'}(
				h::icon('pencil'),
				[
					'href'			=> "$a->action/edit/$id",
					'data-title'	=> $L->edit_group_information
				]
			).
			($id != User::ADMIN_GROUP_ID && $id != User::USER_GROUP_ID && $id != User::BOT_GROUP_ID ? h::{'a.uk-button.cs-button-compact'}(
				h::icon('trash-o'),
				[
					'href'			=> "$a->action/delete/$id",
					'data-title'	=> $L->delete
				]
			) : '').
			h::{'a.uk-button.cs-button-compact'}(
				h::icon('key'),
				[
					'href'			=> "$a->action/permissions/$id",
					'data-title'	=> $L->edit_group_permissions
				]
			),
			$id,
			$group_data['title'],
			$group_data['description']
		];
	}
	unset($id, $group_data, $groups_ids);
	$a->content(
		h::{'cs-table[center][list][with-header] cs-table-row| cs-table-cell'}(
			[
				$L->action,
				'id',
				$L->group_name,
				$L->group_description
			],
			$groups_list
		).
		h::{'p.cs-left a.uk-button'}(
			$L->add_group,
			[
				'href' => "admin/System/$rc[0]/$rc[1]/add"
			]
		)
	);
}
