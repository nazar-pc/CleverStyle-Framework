<?php
global $Config, $Index, $L, $User;
$a				= &$Index;
$rc				= &$Config->routing['current'];
if (isset($rc[2], $rc[3])) {
	switch ($rc[2]) {
		case 'edit':
			$a->apply		= false;
			$a->cancel_back	= true;
			$group_data		= $User->get_group_data($rc[3]);
			$a->content(
				h::{'table.admin_table.center_all'}(
					h::{'tr th.ui-widget-header.ui-corner-all'}([
						'&nbsp;id&nbsp;',
						$L->group_title,
						$L->description,
						'data'
					]).
					h::{'tr td.ui-widget-content.ui-corner-all'}([
						$rc[3],
						h::{'input.form_element'}([
							'name'		=> 'group[title]',
							'value'		=> $group_data['title']
						]),
						h::{'input.form_element'}([
							'name'		=> 'group[description]',
							'value'		=> $group_data['description']
						]),
						h::{'textarea.form_element'}(
							$group_data['data'],
							[
								'name'		=> 'group[data]'
							]
						)
					])
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'group[id]',
					'value'	=> $rc[3]
				])
			);
		break;
		case 'delete':
			$a->buttons		= false;
			$a->cancel_back	= true;
			$permission		= $User->db()->qf('SELECT `title` FROM `[prefix]groups` WHERE `id` = '.(int)$rc[3].' LIMIT 1');
			$a->content(
				h::{'p.center_all'}(
					$L->sure_delete_group($permission['title'])
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
			$a->apply		= false;
			$a->cancel_back	= true;
			global $Cache;
			$permissions	= $Cache->permissions_table;
			$permission		= $User->get_group_permissions($rc[3]);
			$tabs			= [];
			$tabs_content	= '';
			foreach ($permissions as $group => $list) {
				$tabs[]		= h::{'a'}(
					$L->{'permissions_group_'.$group},
					[
						'href'	=> '#permissions_group_'.strtr($group, '/', '_')
					]
				);
				$content	= [];
				foreach($list as $label => $id) {
					$content[] = h::{'th.ui-widget-header.ui-corner-all'}($L->{'permission_label_'.$label}).
						h::{'td input[type=radio]'}([
							'name'			=> 'permission['.$id.']',
							'checked'		=> isset($permission[$id]) ? $permission[$id] : 0,
							'value'			=> [0, 1],
							'in'			=> [$L->deny, $L->allow]
						]);
				}
				if (count($list) % 2) {
					$content[] = h::{'td[colspan=2]'}();
				}
				$count		= count($content);
				$content_	= '';
				for ($i = 0; $i < $count; $i += 2) {
					$content_ .= h::tr(
						$content[$i].
							$content[$i+1]
					);
				}
				unset($content);
				$tabs_content .= h::{'div#permissions_group_'.strtr($group, '/', '_').' table.admin_table.center_all'}(
					h::{'tr td.left_all[colspan=4]'}(
						h::{'button.permissions_group_invert'}($L->invert).
						h::{'button.permissions_group_allow_all'}($L->allow_all).
						h::{'button.permissions_group_deny_all'}($L->deny_all)
					).
					h::tr($content_)
				);
			}
			unset($content);
			$a->content(
				h::{'p.ui-priority-primary.for_state_messages'}(
					$L->permissions_for_group(
						$User->get_group_data($rc[3])['title']
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
	$groups_ids		= $User->db()->qfa('SELECT `id` FROM `[prefix]groups` ORDER BY `id`');
	$groups_list	= h::{'tr th.ui-widget-header.ui-corner-all'}([
		$L->action,
		'id',
		$L->group_title,
		$L->description
	]);
	foreach ($groups_ids as $id) {
		$id = $id['id'];
		$group_data = $User->get_group_data($id);
		$groups_list .= h::{'tr td.ui-widget-content.ui-corner-all'}([
			h::a(
				h::{'button.compact'}(
					h::icon('wrench'),
					[
						'data-title'	=> $L->edit_group_data
					]
				),
				[
					'href'	=> $a->action.'/edit/'.$id
				]
			).
			($id != 1 && $id != 2 && $id != 3 ? h::a(
				h::{'button.compact'}(
					h::icon('trash'),
					[
						'data-title'	=> $L->delete
					]
				),
				[
					'href'	=> $a->action.'/delete/'.$id
				]
			) : '').//TODO make delete function
			h::a(
				h::{'button.compact'}(
					h::icon('flag'),
					[
						'data-title'	=> $L->edit_group_permissions
					]
				),
				[
					'href'	=> $a->action.'/permissions/'.$id
				]
			),
			$id,
			$group_data['title'],
			$group_data['description']
		]);
	}
	unset($id, $group_data, $groups_ids);
	$a->content(
		h::{'table.admin_table.center_all'}(
			$groups_list
		)//TODO make add group function
	//TODO write set and delete group functions in User
	);
}
