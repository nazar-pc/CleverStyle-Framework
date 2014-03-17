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
$Config			= Config::instance();
$L				= Language::instance();
$Page			= Page::instance();
$Permission		= Permission::instance();
$a				= Index::instance();
$rc				= $Config->route;
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'add':
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$Page->title($L->adding_permission);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->adding_permission
				).
				h::{'table.cs-table-borderless.cs-center-all'}(
					h::{'thead tr th'}([
						$L->group,
						$L->label
					]).
					h::{'tbody tr td'}([
						h::{'input[name=permission[group]]'}(),
						h::{'input[name=permission[label]]'}()
					])
				)
			);
		break;
		case 'edit':
			if (!isset($rc[3])) {
				break;
			}
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$permission				= $Permission->get($rc[3]);
			$Page->title(
				$L->editing_permission("$permission[group]/$permission[label]")
			);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->editing_permission("$permission[group]/$permission[label]")
				).
				h::{'table.cs-table-borderless.cs-center-all'}(
					h::{'thead tr th'}([
						'&nbsp;id&nbsp;',
						$L->group,
						$L->label
					]).
					h::{'tbody tr td'}([
						$rc[3],
						h::input([
							'name'		=> 'permission[group]',
							'value'		=> $permission['group']
						]),
						h::input([
							'name'		=> 'permission[label]',
							'value'		=> $permission['label']
						])
					])
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'permission[id]',
					'value'	=> $rc[3]
				])
			);
			$Page->warning($L->changing_settings_warning);
		break;
		case 'delete':
			if (!isset($rc[3])) {
				break;
			}
			$a->buttons				= false;
			$a->cancel_button_back	= true;
			$permission				= $Permission->get($rc[3]);
			$Page->title(
				$L->deletion_of_permission("$permission[group]/$permission[label]")
			);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->sure_delete_permission("$permission[group]/$permission[label]")
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'id',
					'value'	=> $rc[3]
				]).
				h::{'button[type=submit]'}($L->yes)
			);
			$Page->warning($L->changing_settings_warning);
		break;
	}
	$a->content(
		h::{'input[type=hidden]'}([
			'name'	=> 'mode',
			'value'	=> $rc[2]
		])
	);
} else {
	$a->buttons			= false;
	$permissions		= $Permission->get_all();
	$permissions_list	= [
		h::th([$L->action, 'id', $L->group, $L->label]),
		h::th([$L->action, 'id', $L->group, $L->label])
	];
	$count = 0;
	$blocks					= [];
	foreach ($Config->components['blocks'] as $block) {
		$blocks[$block['index']] = $block['title'];
	}
	unset($block);
	foreach ($permissions as $group => $list) {
		foreach ($list as $label => $id) {
			++$count;
			$permissions_list[] = h::{'td.cs-left-all'}([
				h::{'a.cs-button-compact'}(
					h::icon('pencil'),
					[
						'href'			=> "$a->action/edit/$id",
						'data-title'	=> $L->edit
					]
				).
				h::{'a.cs-button-compact'}(
					h::icon('trash-o'),
					[
						'href'			=> "$a->action/delete/$id",
						'data-title'	=> $L->delete
					]
				),
				$id,
				h::span($group),
				h::span(
					$label,
					[
						'data-title'	=> $group == 'Block' ? Text::instance()->process($Config->module('System')->db('texts'), $blocks[$label]) : false
					]
				)
			]);
		}
	}
	if ($count % 2) {
		$permissions_list[] = h::{'td[colspan=4]'}();
	}
	unset($permissions, $group, $list, $label, $id, $blocks);
	$count				= count($permissions_list);
	$permissions_list_	= '';
	for ($i = 0; $i < $count; $i += 2) {
		$permissions_list_ .= h::tr(
			$permissions_list[$i].
			$permissions_list[$i+1]
		);
	}
	unset($permissions_list);
	$a->content(
		h::{'table.cs-table.cs-center-all'}(
			$permissions_list_
		).
		h::{'p.cs-left a.cs-button'}(
			$L->add_permission,
			[
				'href' => "admin/System/$rc[0]/$rc[1]/add"
			]
		)
	);
}
