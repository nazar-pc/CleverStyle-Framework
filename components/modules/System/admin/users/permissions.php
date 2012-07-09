<?php
global $Config, $Index, $L, $User, $Page;
$a				= &$Index;
$rc				= $Config->routing['current'];
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'add':
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$Page->title($L->adding_a_permission);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->adding_a_permission
				).
				h::{'table.cs-fullwidth-table.cs-center-all'}(
					h::{'tr th.ui-widget-header.ui-corner-all'}([
						$L->group,
						$L->label
					]).
					h::{'tr td.ui-widget-content.ui-corner-all'}([
						h::{'input.cs-form-element'}([
							'name'		=> 'permission[group]'
						]),
						h::{'input.cs-form-element'}([
							'name'		=> 'permission[label]'
						])
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
			$permission				= $User->get_permission($rc[3]);
			$Page->title(
				$L->editing_a_permission($permission['group'].'/'.$permission['label'])
			);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->editing_a_permission($permission['group'].'/'.$permission['label'])
				).
				h::{'table.cs-fullwidth-table.cs-center-all'}(
					h::{'tr th.ui-widget-header.ui-corner-all'}([
						'&nbsp;id&nbsp;',
						$L->group,
						$L->label
					]).
					h::{'tr td.ui-widget-content.ui-corner-all'}([
						$rc[3],
						h::{'input.cs-form-element'}([
							'name'		=> 'permission[group]',
							'value'		=> $permission['group']
						]),
						h::{'input.cs-form-element'}([
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
			global $Page;
			$Page->warning($L->changing_settings_warning);
		break;
		case 'delete':
			if (!isset($rc[3])) {
				break;
			}
			$a->buttons				= false;
			$a->cancel_button_back	= true;
			$permission				= $User->get_permission($rc[3]);
			$Page->title(
				$L->deleting_a_permission($permission['group'].'/'.$permission['label'])
			);
			$a->content(
				h::{'p.ui-priority-primary.cs-state-messages'}(
					$L->sure_delete_permission($permission['group'].'/'.$permission['label'])
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
	}
	$a->content(
		h::{'input[type=hidden]'}([
			'name'	=> 'mode',
			'value'	=> $rc[2]
		])
	);
} else {
	$a->buttons			= false;
	global $Cache;
	$permissions		= $User->get_permissions_table();//TODO Groups collapsing
	$permissions_list	= [
		h::{'th.ui-widget-header.ui-corner-all'}([$L->action, 'id', $L->group, $L->label]),
		h::{'th.ui-widget-header.ui-corner-all'}([$L->action, 'id', $L->group, $L->label])
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
			$permissions_list[] = h::{'td.ui-widget-content.ui-corner-all.cs-left-all'}([
				h::a(
					h::{'button.cs-button-compact'}(
						h::icon('wrench'),
						[
							'data-title'	=> $L->edit
						]
					),
					[
						'href'	=> $a->action.'/edit/'.$id
					]
				).
				h::a(
					h::{'button.cs-button-compact'}(
						h::icon('trash'),
						[
							'data-title'	=> $L->delete
						]
					),
					[
						'href'	=> $a->action.'/delete/'.$id
					]
				),
				$id,
				h::span(
					$group,
					[
						'data-title'	=> $L->{'permissions_group_'.$group}
					]
				),
				h::span(
					$label,
					[
						'data-title'	=> $group != 'Block' ? $L->{'permission_label_'.$label} : $blocks[$label]
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
		h::{'table.cs-fullwidth-table.cs-center-all'}(
			$permissions_list_.
			h::{'tr td.cs-left-all[colspan=8] button'}(
				$L->add_permission,
				[
					'onMouseDown' => 'javasript: location.href= \'admin/'.MODULE.'/'.$rc[0].'/'.$rc[1].'/add\';'
				]
			)
		)
	);
}