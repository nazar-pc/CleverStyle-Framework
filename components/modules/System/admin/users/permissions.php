<?php
global $Config, $Index, $L, $User;
$a				= &$Index;
$rc				= &$Config->routing['current'];
if (isset($rc[2], $rc[3])) {
	switch ($rc[2]) {
		case 'edit':
			$a->apply		= false;
			$a->cancel_back	= true;
			$content		= $content_ = '';
			$permission		= $User->db()->qf('SELECT `id`, `label`, `group` FROM `[prefix]permissions` WHERE `id` = '.(int)$rc[3].' LIMIT 1');
			$a->content(
				h::{'table.admin_table.center_all'}(
					h::{'tr th.ui-widget-header.ui-corner-all'}([
						'&nbsp;id&nbsp;',
						$L->group,
						$L->label
					]).
					h::{'tr td.ui-widget-content.ui-corner-all'}([
						$rc[3],
						h::{'input.form_element'}([
							'name'		=> 'permission[group]',
							'value'		=> $permission['group']
						]),
						h::{'input.form_element'}([
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
			$a->buttons		= false;
			$a->cancel_back	= true;
			$permission		= $User->db()->qf('SELECT `label`, `group` FROM `[prefix]permissions` WHERE `id` = '.(int)$rc[3].' LIMIT 1');
			$a->content(
				h::{'p.center_all'}(
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
	foreach ($permissions as $group => $list) {
		foreach ($list as $label => $id) {
			++$count;
			$permissions_list[] = h::{'td.ui-widget-content.ui-corner-all.left_all'}([
				h::a(
					h::{'button.compact'}(
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
					h::{'button.compact'}(
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
						'data-title'	=> $L->{'permission_label_'.$label}//TODO labels in language file
					]
				)
			]);
		}
	}
	if ($count % 2) {
		$permissions_list[] = h::{'td[colspan=4]'}();
	}
	unset($permissions, $group, $list, $label, $id);
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
		h::{'table.admin_table.center_all'}(
			$permissions_list_
		)//TODO make add permission function
	//TODO write check permission function in Index
	);
}