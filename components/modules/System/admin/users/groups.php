<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $Index, $L, $User, $Page;
$a				= $Index;
$rc				= $Config->route;
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'add':
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$Page->title($L->adding_a_group);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->adding_a_group
				).
				h::{'table.cs-fullwidth-table.cs-center-all tr'}(
					h::{'th.ui-widget-header.ui-corner-all'}(
						$L->group_name,
						$L->description
					),
					h::{'td.ui-widget-content.ui-corner-all'}(
						h::input([
							'name'		=> 'group[title]'
						]),
						h::input([
							'name'		=> 'group[description]'
						])
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
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->editing_of_group($group_data['title'])
				).
				h::{'table.cs-fullwidth-table.cs-center-all tr'}(
					h::{'th.ui-widget-header.ui-corner-all'}(
						'&nbsp;id&nbsp;',
						$L->group_name,
						$L->description,
						'data'
					),
					h::{'td.ui-widget-content.ui-corner-all'}(
						$rc[3],
						h::input([
							'name'		=> 'group[title]',
							'value'		=> $group_data['title']
						]),
						h::input([
							'name'		=> 'group[description]',
							'value'		=> $group_data['description']
						]),
						h::textarea(
							$group_data['data'],
							[
								'name'		=> 'group[data]'
							]
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
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->sure_delete_group($group['title'])
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'id',
					'value'	=> $rc[3]
				]).
				h::{'button[type=submit]'}($L->yes)
			);
			global $Page;
			$Page->warning($L->changing_settings_warning);
		break;
		case 'permissions':
			if (!isset($rc[3])) {
				break;
			}
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			global $Cache;
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
					$L->{'permissions_group_'.$group},
					[
						'href'	=> '#permissions_group_'.strtr($group, '/', '_')
					]
				);
				$content	= [];
				foreach($list as $label => $id) {
					$content[] = h::{'th.ui-widget-header.ui-corner-all'}(
						$group != 'Block' ? $L->{'permission_label_'.$label} : $blocks[$label]
					).
					h::{'td input[type=radio]'}([
						'name'			=> 'permission['.$id.']',
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
				$tabs_content .= h::{'div#permissions_group_'.strtr($group, '/', '_').' table.cs-fullwidth-table.cs-center-all tr'}(
					h::{'td.cs-left-all[colspan=4]'}(
						h::{'button.cs-permissions-invert'}($L->invert).
						h::{'button.cs-permissions-allow-all'}($L->allow_all).
						h::{'button.cs-permissions-deny-all'}($L->deny_all)
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
				h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
					$L->permissions_for_group(
						$User->get_group($rc[3], 'title')
					)
				).
				h::{'div#group_permissions_tabs'}(
					h::{'ul li'}($tabs).
					$tabs_content
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
	$groups_list	= [h::{'th.ui-widget-header.ui-corner-all'}(
		$L->action,
		'id',
		$L->group_name,
		$L->description
	)];
	foreach ($groups_ids as $id) {
		$id				= $id['id'];
		$group_data 	= $User->get_group($id);
		$groups_list[]	= h::{'td.ui-widget-content.ui-corner-all'}(
			h::{'a.cs-button-compact'}(
				h::icon('wrench'),
				[
					'href'			=> $a->action.'/edit/'.$id,
					'data-title'	=> $L->edit_group_information
				]
			).
			($id != 1 && $id != 2 && $id != 3 ? h::{'a.cs-button-compact'}(
				h::icon('trash'),
				[
					'href'			=> $a->action.'/delete/'.$id,
					'data-title'	=> $L->delete
				]
			) : '').
			h::{'a.cs-button-compact'}(
				h::icon('key'),
				[
					'href'			=> $a->action.'/permissions/'.$id,
					'data-title'	=> $L->edit_group_permissions
				]
			),
			$id,
			$group_data['title'],
			$group_data['description']
		);
	}
	unset($id, $group_data, $groups_ids);
	$a->content(
		h::{'table.cs-fullwidth-table.cs-center-all tr'}(
			$groups_list,
			h::{'td.cs-left-all[colspan=4] a.cs-button'}(
				$L->add_group,
				[
					'href' => 'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1].'/add'
				]
			)
		)
	);
}
