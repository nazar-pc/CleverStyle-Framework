<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$User	= User::instance();
$a		= Index::instance();
$rc		= $Config->route;
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'add':
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$Page->title($L->adding_a_group);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->adding_a_group
				).
				h::{'table.cs-table-borderless.cs-center-all tr'}(
					h::{'thead tr th'}(
						$L->group_name,
						$L->description
					),
					h::{'tbody tr td'}(
						h::{'input[name=group[title]]'}(),
						h::{'input[name=group[description]]'}()
					)
				)
			);
		break;
		case 'edit':
			if (!isset($rc[3])) {
				break;
			}
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$group_data				= $User->get_group($rc[3]);
			$Page->title(
				$L->editing_of_group($group_data['title'])
			);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->editing_of_group($group_data['title'])
				).
				h::{'table.cs-table-borderless.cs-center-all tr'}(
					h::{'thead tr th'}(
						'&nbsp;id&nbsp;',
						$L->group_name,
						$L->description,
						'data'
					),
					h::{'tbody tr td'}(
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
					)
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
			$group					= $User->get_group($rc[3]);
			$Page->title(
				$L->deletion_of_group($group['title'])
			);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->sure_delete_group($group['title'])
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'id',
					'value'	=> $rc[3]
				]).
				h::{'button[type=submit]'}($L->yes)
			);
			$Page->warning($L->changing_settings_warning);
		break;
		case 'permissions':
			if (!isset($rc[3])) {
				break;
			}
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$permissions			= $User->get_permissions_table();
			$permission				= $User->get_group_permissions($rc[3]);
			$tabs					= [];
			$tabs_content			= '';
			$blocks					= [];
			foreach ($Config->components['blocks'] as $block) {
				$blocks[$block['index']] = $block['title'];
			}
			unset($block);
			foreach ($permissions as $group => $list) {
				$tabs[]		= h::a(
					$L->{"permissions_group_$group"},
					[
						'href'	=> '#permissions_group_'.strtr($group, '/', '_')
					]
				);
				$content	= [];
				foreach($list as $label => $id) {
					$content[] = h::th(
						$group != 'Block' ? $L->{"permission_label_$label"} : Text::instance()->process($Config->module('System')->db('texts'), $blocks[$label])
					).
					h::{'td input[type=radio]'}([
						'name'			=> "permission[$id]",
						'checked'		=> isset($permission[$id]) ? $permission[$id] : -1,
						'value'			=> [-1, 0, 1],
						'in'			=> [$L->not_specified, $L->deny, $L->allow]
					]);
				}
				if (count($list) % 2) {
					$content[] = h::{'td[colspan=2]'}();
				}
				$count		= count($content);
				$content_	= [];
				for ($i = 0; $i < $count; $i += 2) {
					$content_[]	= $content[$i].$content[$i+1];
				}
				$tabs_content .= h::{'div#permissions_group_'.strtr($group, '/', '_').' table.cs-table-borderless.cs-center-all tr'}(
					h::{'td.cs-left-all[colspan=4]'}(
						h::{'button.cs-permissions-invert'}($L->invert).
						h::{'button.cs-permissions-deny-all'}($L->deny_all).
						h::{'button.cs-permissions-allow-all'}($L->allow_all)
					),
					$content_
				);
			}
			unset($content, $content_, $count, $i, $permissions, $group, $list, $label, $id, $blocks);
			$Page->title(
				$L->permissions_for_group(
					$User->get_group($rc[3], 'title')
				)
			);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->permissions_for_group(
						$User->get_group($rc[3], 'title')
					)
				).
				h::{'div.cs-tabs'}(
					h::{'ul li'}($tabs).
					h::div($tabs_content)
				).
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
	$groups_ids		= $User->get_groups_list();
	$groups_list	= [];
	foreach ($groups_ids as $id) {
		$id				= $id['id'];
		$group_data 	= $User->get_group($id);
		$groups_list[]	= [
			h::{'a.cs-button-compact'}(
				h::icon('edit'),
				[
					'href'			=> "$a->action/edit/$id",
					'data-title'	=> $L->edit_group_information
				]
			).
			($id != 1 && $id != 2 && $id != 3 ? h::{'a.cs-button-compact'}(
				h::icon('trash'),
				[
					'href'			=> "$a->action/delete/$id",
					'data-title'	=> $L->delete
				]
			) : '').
			h::{'a.cs-button-compact'}(
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
		h::{'table.cs-table.cs-center-all'}(
			h::{'thead tr th'}(
				$L->action,
				'id',
				$L->group_name,
				$L->description
			).
			h::{'tbody tr| td'}($groups_list)
		).
		h::{'p.cs-left a.cs-button'}(
			$L->add_group,
			[
				'href' => "admin/System/$rc[0]/$rc[1]/add"
			]
		)
	);
}
