<?php
global $Config, $Index, $L, $Cache, $User;
$a				= &$Index;
$rc				= &$Config->routing['current'];
$search_columns	= $Cache->users_columns;
if (isset($rc[2], $rc[3])) {
	switch ($rc[2]) {
		case 'edit_raw':
			$a->apply		= false;
			$a->cancel_back	= true;
			$content		= $content_ = '';
			$user_data		= $User->get($search_columns, $rc[3]);
			$last			= count($search_columns)-1;
			foreach ($search_columns as $i => $column) {
				$content_ .= h::{'th.ui-widget-header.ui-corner-all'}(
					$column
				).
				h::{'td.ui-widget-content.ui-corner-all'}(
					$column == 'data' || $column == 'about' ?
						h::{'textarea.form_element'}(
							$user_data[$column],
							[
								'name'		=> 'user['.$column.']'
							]
						) :
						h::{'input.form_element'}([
							'name'		=> 'user['.$column.']',
							'value'		=> $user_data[$column],
							$column == 'id' ? 'readonly' : false
						]),
					[
						'colspan'	=> $i == $last ? 3 : false
					]
				);
				if  ($i % 2) {
					$content .= h::tr(
						$content_
					);
					$content_ = '';
				}
			}
			if ($content_ != '') {
				$content .= h::tr(
					$content_
				);
			}
			unset($i, $column, $content_);
			$a->content(
				h::{'table#users_raw_edit.admin_table.center_all'}($content)
			);
		break;
		case 'edit':
			$a->apply		= false;
			$a->cancel_back	= true;
			$user_data		= $User->get(
				[
					'login',
					'username',
					'email',
					'language',
					'timezone',
					'reg_date',
					'reg_ip',
					'status',
					'block_until',
					'last_login',
					'lastip',
					'gender',
					/*'country',
					'region',
					'district',
					'city',*/
					'birthday',
					'avatar',
					'website',
					'icq',
					'skype',
					'about'
				],
				$rc[3]
			);
			$timezones	= get_timezones_list();
			$reg_ip		= hex2ip($user_data['reg_ip'], 10);
			$lastip		= hex2ip($user_data['lastip'], 10);
			$row		= function ($row1, $row2) {
				return	h::{'th.ui-widget-header.ui-corner-all'}($row1).
						h::{'td.ui-widget-content.ui-corner-all'}($row2);
			};
			$a->content(
				h::{'table#users_edit.admin_table.center_all tr'}([
					$row('id', $rc[3]),

					$row($L->registration_date, $user_data['reg_date'] ? date($L->_date, $user_data['reg_date']) : $L->undefined),

					$row($L->registration_ip, $reg_ip[0] ? $reg_ip[0].($reg_ip[1] ? h::br().$reg_ip[1] : '') : $L->undefined),

					$row($L->last_login, $user_data['last_login'] ? date($L->_datetime, $user_data['last_login']) : $L->undefined),

					$row($L->last_ip, $lastip[0] ? $lastip[0].($lastip[1] ? h::br().$lastip[1] : '') : $L->undefined),

					$row($L->login, h::{'input.form_element'}([
						'name'		=> 'user[login]',
						'value'		=> $user_data['login']
					])),

					$row($L->username, h::{'input.form_element'}([
						'name'	=> 'user[username]',
						'value'	=> $user_data['username']
					])),

					$row($L->email, h::{'input.form_element'}([
						'name'		=> 'user[email]',
						'value'		=> $user_data['email']
					])),

					$row(
						$L->password_only_for_changing.h::{'icon#show_password'}('locked'),
						h::{'input.form_element[type=password]'}([
							'id'	=> 'user_password',
							'name'	=> 'user[password]',
							'value'	=> ''
						])
					),

					$row($L->language, h::{'select.form_element'}(
						[
							'in'		=> array_merge([$L->system_default.' ('.$Config->core['language'].')'], $Config->core['active_languages']),
							'value'		=> array_merge([''], $Config->core['active_languages'])
						],
						[
							'name'		=> 'user[language]',
							'selected'	=> $user_data['language'],
							'size'		=> 5
						]
					)),

					$row($L->timezone, h::{'select.form_element'}(
						[
							'in'		=> array_merge([$L->system_default.' ('.$Config->core['timezone'].')'], array_values($timezones)),
							'value'		=> array_merge([''], array_keys($timezones))
						],
						[
							'name'		=> 'user[timezone]',
							'selected'	=> $user_data['timezone'],
							'size'		=> 5
						]
					)),

					$row($L->status, h::{'input.form_element[type=radio]'}([
						'name'		=> 'user[status]',
						'checked'	=> $user_data['status'],
						'value'		=> [-1, 0, 1],
						'in'		=> [$L->is_not_activated, $L->inactive, $L->active]
					])),

					$row(h::info('block_until'), h::{'input.form_element[type=datetime-local]'}([
						'name'		=> 'user[block_until]',
						'value'		=> date('Y-m-d\TH:i', $user_data['block_until'] ?: TIME)
					])),

					$row($L->gender, h::{'input.form_element[type=radio]'}([
						'name'		=> 'user[gender]',
						'checked'	=> $user_data['gender'],
						'value'		=> [-1, 0, 1],
						'in'		=> [$L->undefined, $L->male, $L->female]
					])),

					$row(h::info('birthday'), h::{'input.form_element[type=date]'}([
						'name'		=> 'user[birthday]',
						'value'		=> date('Y-m-d', $user_data['birthday'] ?: TIME)
					])),

					$row($L->avatar, h::{'input.form_element'}([
						'name'		=> 'user[avatar]',
						'value'		=> $user_data['avatar']
					])),

					$row($L->website, h::{'input.form_element'}([
						'name'		=> 'user[website]',
						'value'		=> $user_data['website']
					])),

					$row($L->icq, h::{'input.form_element'}([
						'name'		=> 'user[icq]',
						'value'		=> $user_data['icq'] ?: ''
					])),

					$row($L->skype, h::{'input.form_element'}([
						'name'		=> 'user[skype]',
						'value'		=> $user_data['skype']
					])),

					$row($L->about_myself, h::{'textarea.form_element'}([
						'name'		=> 'user[about]',
						'value'		=> $user_data['about']
					]))
				]).
				h::{'input[type=hidden]'}([
					'name'	=> 'user[id]',
					'value'	=> $rc[3]
				])
			);
		break;
		case 'deactivate':
			$a->buttons		= false;
			$a->cancel_back	= true;
			$user_data		= $User->get(['login', 'username'], $rc[3]);
			$a->content(
				h::{'p.center_all'}(
					$L->sure_deactivate_user($user_data['username'] ?: $user_data['login'])
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'id',
					'value'	=> $rc[3]
				]).
				h::{'button[type=submit]'}($L->yes)
			);
		break;
		case 'activate':
			$a->buttons		= false;
			$a->cancel_back	= true;
			$user_data		= $User->get(['login', 'username'], $rc[3]);
			$a->content(
				h::{'p.center_all'}(
					$L->sure_activate_user($user_data['username'] ?: $user_data['login'])
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'id',
					'value'	=> $rc[3]
				]).
				h::{'button[type=submit]'}($L->yes)
			);
		break;
		case 'permissions':
			if (!isset($rc[3])) {
				break;
			}
			$a->apply		= false;
			$a->cancel_back	= true;
			global $Cache;
			$permissions	= $User->get_permissions_table();;
			$permission		= $User->get_user_permissions($rc[3]);
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
							'checked'		=> isset($permission[$id]) ? $permission[$id] : -1,
							'value'			=> [-1, 0, 1],
							'in'			=> [$L->inherited, $L->deny, $L->allow]
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
			$User->get(['username', 'login', 'email'], $rc[3]);
			$a->content(
				h::{'p.ui-priority-primary.for_state_messages'}(
					$L->permissions_for_user(
						$User->get('username', $rc[3]) ?: $User->get('login', $rc[3]) ?: $User->get('email', $rc[3])
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
	$u_db			= $User->db();
	$columns		= isset($_POST['columns']) && $_POST['columns'] ? explode(';', $_POST['columns']) : [
		'id', 'login', 'username', 'email'
	];
	$limit			= isset($_POST['search_limit'])	? (int)$_POST['search_limit']	: 100;
	$start			= isset($_POST['search_start'])	? (int)$_POST['search_start']-1	: 0;
	$search_text	= isset($_POST['search_text'])	? $_POST['search_text']			: '';
	$columns_list	= '';
	$search_modes	= [
		'=', '!=', '>', '<', '>=', '<=',
		'LIKE', 'NOT LIKE', 'IN', 'NOT IN',
		'IS NULL', 'IS NOT NULL', 'REGEXP', 'NOT REGEXP'
	];
	$search_mode	= isset($_POST['search_mode']) && in_array($_POST['search_mode'], $search_modes) ? $_POST['search_mode'] : '';
	foreach ($search_columns as $column) {
		$columns_list .= h::li(
			$column,
			[
				'style'	=> 'display: inline-block;',
				'class'	=> in_array($column, $columns) ? 'ui-selected' : ''
			]
		);
	}
	unset($column);
	$columns		= array_intersect($search_columns, $columns);
	$search_column	= isset($_POST['search_column']) && in_array($_POST['search_column'], $search_columns) ? $_POST['search_column'] : '';
	//Closures for constructing WHERE part of SQL query
	if ($search_column) {
		$where_func = function ($in) {
			return str_replace('%%', $_POST['search_column'], $in);
		};
	} else {
		$where_func = function ($in) use (&$search_columns) {
			$return = [];
			foreach ($search_columns as $column) {
				$return[] = str_replace('%%', $column, $in);
			}
			return implode(' OR ', $return);
		};
	}
	//Applying (if necessary) filter
	$where = 1;
	if ($search_text && $search_mode) {
		switch ($_POST['search_mode']) {
			case '=':
			case '!=':
			case '>':
			case '<':
			case '>=':
			case '<=':
			case 'LIKE':
			case 'NOT LIKE':
			case 'REGEXP':
			case 'NOT REGEXP':
				$search_text_ = $u_db->sip($search_text);
				$where = $where_func('`%%` '.$search_mode." ".$search_text_);
				unset($search_text_);
				break;
			case 'IN':
			case 'NOT IN':
				$search_text_ = "'".implode("', '", _trim(explode(',', $search_text), "\n'"))."'";
				$where = $where_func('`%%` '.$search_mode.' ('.$search_text_.')');
				unset($search_text_);
				break;
		}
	}
	$results_count	= $u_db->qf('SELECT COUNT(`id`) AS `count` FROM `[prefix]users` WHERE '.$where);
	if ($results_count = $results_count['count']) {
		$users_ids = $u_db->qfa(
			'SELECT `id` FROM `[prefix]users` WHERE '.$where.' ORDER BY `id` LIMIT '.($start*$limit).', '.$limit
		);
	}
	$users_list				= h::{'tr th.ui-widget-header.ui-corner-all'}(
		array_merge([$L->action, ''], $columns)
	);
	$users_list_template	= h::{'tr td.ui-widget-content.ui-corner-all'}(
		array_fill(0, count($columns)+2, '%s')
	);
	if (isset($users_ids) && is_array($users_ids)) {
		foreach ($users_ids as $id) {
			$id = $id['id'];
			$action = h::a(
				h::{'button.compact'}(
					h::icon('pencil'),
					[
						'data-title'	=> $L->edit_raw_user_data
					]
				),
				[
					'href'		=> $a->action.'/edit_raw/'.$id
				]
			).
			h::a(
				h::{'button.compact'}(
					h::icon('wrench'),
					[
						'data-title'	=> $L->edit_user_data
					]
				),
				[
					'href'		=> $a->action.'/edit/'.$id
				]
			).
			($id != 1 && $id != 2 ?
				h::a(
					h::{'button.compact'}(
						h::icon($User->get('status', $id) == 1 ? 'minusthick' : 'check'),
						[
							'data-title'	=> $L->deactivate_user
						]
					),
					[
						'href'		=> $a->action.'/'.($User->get('status', $id) == 1 ? 'deactivate' : 'activate').'/'.$id
					]
				) : ''
			).
			h::a(
				h::{'button.compact'}(
					h::icon('flag'),
					[
						'data-title'	=> $L->edit_user_permissions
					]
				),
				[
					'href'	=> $a->action.'/permissions/'.$id
				]
			);
			$user_data		= $User->get($columns, $id);
			if (isset($user_data['reg_ip'])) {
				$user_data['reg_ip'] = hex2ip($user_data['reg_ip'], 10);
				if ($user_data['reg_ip'][1]) {
					$user_data['reg_ip'] = $user_data['reg_ip'][0].h::br().$user_data['reg_ip'][1];
				} else {
					$user_data['reg_ip'] = $user_data['reg_ip'][0];
				}
			}
			if (isset($user_data['lastip'])) {
				$user_data['lastip'] = hex2ip($user_data['lastip'], 10);
				if ($user_data['lastip'][1]) {
					$user_data['lastip'] = $user_data['lastip'][0].h::br().$user_data['lastip'][1];
				} else {
					$user_data['lastip'] = $user_data['lastip'][0];
				}
			}
			$groups			= $User->get_user_groups($id);
			if (in_array(1, $groups)) {
				$type = h::info('a');
			} elseif (in_array(2, $groups)) {
				$type = h::info('u');
			} elseif (in_array(3, $groups)) {
				$type = h::info('b');
			} else {
				$type = h::info('g');
			}
			$users_list		.= vsprintf($users_list_template, array($action, $type)+$user_data);
		}
	}
	unset($users_list_template, $id, $action, $user_data, $users_ids);
	$a->content(
		h::{'div#search_users_tabs'}(
			h::ul(
				h::{'li a'}(
					$L->search,
					[
						'href' => '#search_settings'
					]
				).
				h::{'li a'}(
					h::info('show_columns'),
					[
						'href' => '#columns_settings'
					]
				)
			).
			h::{'div#search_settings'}(
				h::{'select.form_element'}(
					[
						'in'		=> array_merge([$L->all_columns], $search_columns),
						'values'	=> array_merge([''], $search_columns)
					],
					[
						'selected'	=> $search_column ?: '',
						'name'		=> 'search_column'
					]
				).
				$L->search_mode.' '.
				h::{'select.form_element'}(
					$search_modes,
					[
						'selected'	=> $search_mode ?: 'LIKE',
						'name'		=> 'search_mode'
					]
				).
				h::{'input.form_element'}(
					[
						'value'			=> $search_text,
						'name'			=> 'search_text',
						'placeholder'	=> $L->search_text
					]
				).
				$L->page.' '.
				h::{'input.form_element[type=number]'}(
					[
						'value'	=> $start+1,
						'min'	=> 1,
						'size'	=> 4,
						'name'	=> 'search_start'
					]
				).
				$L->items.' '.
				h::{'input.form_element[type=number]'}(
					[
						'value'	=> $limit,
						'min'	=> 1,
						'size'	=> 5,
						'name'	=> 'search_limit'
					]
				),
				[
					'style'	=> 'text-align: left;'
				]
			).
			h::{'div#columns_settings'}(
				h::ol(
					$columns_list
				).
				h::{'input#columns[type=hidden]'}([
					'name'	=> 'columns'
				])
			)
		).
		h::{'button[type=submit'}(
			$L->search,
			[
				'style'	=> 'margin: 5px 100% 5px 0;'
			]
		).
		h::{'p.left'}(
			$L->founded_users($results_count).
			($results_count > $limit ? ' / '.$L->page_from($start+1, ceil($results_count/$limit)) : '')
		).
		h::{'table.admin_table.center_all'}(
			$users_list
		).
		h::{'p.left'}(
			$L->founded_users($results_count).
			($results_count > $limit ? ' / '.$L->page_from($start+1, ceil($results_count/$limit)) : '')
		)//TODO make add user function
	);
}