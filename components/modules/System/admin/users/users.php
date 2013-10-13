<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System\users\users;
use			h,
			cs\Config,
			cs\Group,
			cs\Index,
			cs\Language,
			cs\Page,
			cs\Permission,
			cs\Text,
			cs\User;
function row ($col1, $col2) {
	return	h::th($col1).
			h::td($col2);
}
$Config			= Config::instance();
$L				= Language::instance();
$Page			= Page::instance();
$User			= User::instance();
$a				= Index::instance();
$rc				= $Config->route;
$search_columns	= $User->get_users_columns();
if (isset($rc[2], $rc[3])) {
	$is_bot = in_array(3, (array)$User->get_groups($rc[3]));
	switch ($rc[2]) {
		case 'add':
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$Page->title($L->adding_a_user);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->adding_a_user
				).
				h::{'p.cs-center input'}([
					'name'			=> 'email',
					'placeholder'	=> $L->email
				])
			);
		break;
		case 'add_bot':
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$Page->title($L->adding_a_bot);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->adding_a_bot
				).
				h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr| td'}([
					[
						$L->bot_name,
						h::{'input[name=name]'}()
					],
					[
						h::info('bot_user_agent'),
						h::{'input[name=user_agent]'}()
					],
					[
						h::info('bot_ip'),
						h::{'input[name=ip]'}()
					]
				])
			);
		break;
		case 'edit_raw':
			if ($is_bot || $rc[3] == 1 || $rc[3] == 2) {
				break;
			}
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$content				= $content_ = '';
			$user_data				= $User->get($search_columns, $rc[3]);
			$last					= count($search_columns)-1;
			foreach ($search_columns as $i => $column) {
				$content_ .= h::th($column).
				h::td(
					$column == 'data' || $column == 'about' ?
						h::textarea(
							$user_data[$column],
							[
								'name'		=> "user[$column]"
							]
						) :
						h::input([
							'name'		=> "user[$column]",
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
			$Page->title(
				$L->editing_raw_data_of_user($User->username($rc[3]))
			);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->editing_raw_data_of_user(
						$User->username($rc[3])
					)
				).
				h::{'table.cs-table-borderless.cs-center-all'}($content)
			);
		break;
		case 'edit':
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			if (!$is_bot) {
				if ($rc[3] == 1 || $rc[3] == 2) {
					break;
				}
				$user_data				= $User->get(
					[
						'login',
						'username',
						'email',
						'language',
						'theme',
						'timezone',
						'reg_date',
						'reg_ip',
						'status',
						'block_until',
						'last_login',
						'last_ip',
						'last_online',
						'gender',
						'birthday',
						'avatar',
						'website',
						'skype',
						'about'
					],
					$rc[3]
				);
				$timezones				= get_timezones_list();
				$reg_ip					= hex2ip($user_data['reg_ip'], 10);
				$last_ip				= hex2ip($user_data['last_ip'], 10);
				$themes					= [
					"$L->system_default ({$Config->core['theme']} - {$Config->core['color_scheme']})" => ''
				];
				foreach ($Config->core['active_themes'] as $theme) {
					foreach ($Config->core['color_schemes'][$theme] as $color_scheme) {
						$themes["$theme - $color_scheme"] = _json_encode([
							'theme'			=> $theme,
							'color_scheme'	=> $color_scheme
						]);
					}
				}
				unset($theme, $color_scheme);
				$Page->title(
					$L->editing_of_user_information($User->username($rc[3]))
				);
				$a->content(
					h::{'p.lead.cs-center'}(
						$L->editing_of_user_information(
							$User->username($rc[3])
						)
					).
					h::{'table.cs-table-borderless.cs-center-all tr'}([
						row('id', $rc[3]),
						row($L->registration_date, $user_data['reg_date'] ? date($L->_date, $user_data['reg_date']) : $L->undefined),
						row($L->registration_ip, $reg_ip[0] ? $reg_ip[0].($reg_ip[1] ? h::br().$reg_ip[1] : '') : $L->undefined),
						row($L->last_login, $user_data['last_login'] ? date($L->_datetime, $user_data['last_login']) : $L->undefined),
						row($L->last_ip, $last_ip[0] ? $last_ip[0].($last_ip[1] ? h::br().$last_ip[1] : '') : $L->undefined),
						row($L->last_online, $user_data['last_online'] ? date($L->_datetime, $user_data['last_online']) : $L->undefined),
						row($L->login, h::input([
							'name'		=> 'user[login]',
							'value'		=> $user_data['login']
						])),
						row($L->username, h::input([
							'name'	=> 'user[username]',
							'value'	=> $user_data['username']
						])),
						row($L->email, h::input([
							'name'		=> 'user[email]',
							'value'		=> $user_data['email']
						])),
						row(
							$L->password_only_for_changing.h::{'icon.cs-show-password.cs-pointer'}('lock'),
							h::{'input[type=password]'}([
								'name'	=> 'user[password]',
								'value'	=> ''
							])
						),
						row($L->language, h::select(
							[
								'in'		=> array_merge(["$L->system_default ({$Config->core['language']})"], $Config->core['active_languages']),
								'value'		=> array_merge([''], $Config->core['active_languages'])
							],
							[
								'name'		=> 'user[language]',
								'selected'	=> $user_data['language'],
								'size'		=> 5
							]
						)),
						row($L->theme, h::select(
							[
								'in'		=> array_keys($themes),
								'value'		=> array_values($themes)
							],
							[
								'name'		=> 'user[theme]',
								'selected'	=> $user_data['theme'],
								'size'		=> 5
							]
						)),
						row($L->timezone, h::select(
							[
								'in'		=> array_merge(["$L->system_default ({$Config->core['timezone']})"], array_keys($timezones)),
								'value'		=> array_merge([''], array_values($timezones))
							],
							[
								'name'		=> 'user[timezone]',
								'selected'	=> $user_data['timezone'],
								'size'		=> 5
							]
						)),
						row($L->status, h::{'input[type=radio]'}([
							'name'		=> 'user[status]',
							'checked'	=> $user_data['status'],
							'value'		=> [-1, 0, 1],
							'in'		=> [$L->is_not_activated, $L->inactive, $L->active]
						])),
						row(h::info('block_until'), h::{'input[type=datetime-local]'}([
							'name'		=> 'user[block_until]',
							'value'		=> date('Y-m-d\TH:i', $user_data['block_until'] ?: TIME)
						])),
						row($L->gender, h::{'input[type=radio]'}([
							'name'		=> 'user[gender]',
							'checked'	=> $user_data['gender'],
							'value'		=> [-1, 0, 1],
							'in'		=> [$L->undefined, $L->male, $L->female]
						])),
						row(h::info('birthday'), h::{'input[type=date]'}([
							'name'		=> 'user[birthday]',
							'value'		=> date('Y-m-d', $user_data['birthday'] ?: TIME)
						])),
						row($L->avatar, h::input([
							'name'		=> 'user[avatar]',
							'value'		=> $user_data['avatar']
						])),
						row($L->website, h::input([
							'name'		=> 'user[website]',
							'value'		=> $user_data['website']
						])),
						row($L->skype, h::input([
							'name'		=> 'user[skype]',
							'value'		=> $user_data['skype']
						])),
						row($L->about_me, h::textarea(
							$user_data['about'],
							[
								'name'		=> 'user[about]',
							]
						))
					]).
					h::{'input[type=hidden]'}([
						'name'	=> 'user[id]',
						'value'	=> $rc[3]
					])
				);
			} else {
				$bot_data	= $User->get(
					[
						'login',
						'email',
						'username'
					],
					$rc[3]
				);
				$Page->title(
					$L->editing_of_bot_information($bot_data['username'])
				);
				$a->content(
					h::{'p.lead.cs-center'}(
						$L->editing_of_bot_information(
							$bot_data['username']
						)
					).
					h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr| td'}([
						[
							$L->bot_name,
							h::input([
								'name'	=> 'bot[name]',
								'value'	=> $bot_data['username']
							])
						],
						[
							h::info('bot_user_agent'),
							h::input([
								'name'	=> 'bot[user_agent]',
								'value'	=> $bot_data['login']
							])
						],
						[
							h::info('bot_ip'),
							h::input([
								'name'	=> 'bot[ip]',
								'value'	=> $bot_data['email']
							])
						]
					]).
					h::{'input[type=hidden]'}([
						'name'	=> 'bot[id]',
						'value'	=> $rc[3]
					])
				);
			}
		break;
		case 'deactivate':
			if ($rc[3] == 1 || $rc[3] == 2) {
				break;
			}
			$a->buttons				= false;
			$a->cancel_button_back	= true;
			$user_data				= $User->get(['login', 'username'], $rc[3]);
			$a->content(
				h::{'p.cs-center-all'}(
					$L->{$is_bot ? 'sure_deactivate_bot' : 'sure_deactivate_user'}($user_data['username'] ?: $user_data['login'])
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'id',
					'value'	=> $rc[3]
				]).
				h::{'button[type=submit]'}($L->yes)
			);
		break;
		case 'activate':
			if ($rc[3] == 1 || $rc[3] == 2) {
				break;
			}
			$a->buttons				= false;
			$a->cancel_button_back	= true;
			$user_data				= $User->get(['login', 'username'], $rc[3]);
			$a->content(
				h::{'p.cs-center-all'}(
					$L->{$is_bot ? 'sure_activate_bot' : 'sure_activate_user'}($user_data['username'] ?: $user_data['login'])
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'id',
					'value'	=> $rc[3]
				]).
				h::{'button[type=submit]'}($L->yes)
			);
		break;
		case 'permissions':
			if (!isset($rc[3]) || $rc[3] == 2) {
				break;
			}
			$a->apply_button		= false;
			$a->cancel_button_back	= true;
			$permissions			= Permission::instance()->get_all();
			$user_permissions		= $User->get_permissions($rc[3]);
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
						'checked'		=> isset($user_permissions[$id]) ? $user_permissions[$id] : -1,
						'value'			=> [-1, 0, 1],
						'in'			=> [
							$L->inherited.' ('.(isset($user_permissions[$id]) && !$user_permissions[$id] ? '-' : '+').')',
							$L->deny,
							$L->allow
						]
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
				$tabs_content .= h::{'div#permissions_group_'.strtr($group, '/', '_').' table.cs-table-borderless.cs-center-all'}(
					h::{'tr td.cs-left-all[colspan=4]'}(
						h::{'button.cs-permissions-invert'}($L->invert).
						h::{'button.cs-permissions-deny-all'}($L->deny_all).
						h::{'button.cs-permissions-allow-all'}($L->allow_all)
					).
					h::tr($content_)
				);
			}
			unset($content, $content_, $count, $i, $permissions, $group, $list, $label, $id, $blocks);
			$Page->title($L->{$is_bot ? 'permissions_for_bot' : 'permissions_for_user'}(
				$User->username($rc[3])
			));
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->{$is_bot ? 'permissions_for_bot' : 'permissions_for_user'}(
						$User->username($rc[3])
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
		case 'groups':
			if (!isset($rc[3]) || $rc[3] == 2 || $is_bot) {
				break;
			}
			$a->apply_button		= false;
			$a->reset_button		= false;
			$a->cancel_button_back	= true;
			$Group					= Group::instance();
			$user_groups			= array_reverse($User->get_groups($rc[3]));
			$all_groups				= $User->get_all();
			$groups_selected		= h::{'li.uk-button-primary'}(
				$L->selected_groups
			);
			$groups_list			= h::{'li.uk-button-primary'}(
				$L->other_groups
			);
			if (is_array($user_groups) && !empty($user_groups)) {
				foreach ($user_groups as $group) {
					$group				= $Group->get($group);
					$groups_selected	.= h::{'li.uk-button-success'}(
						$group['title'],
						[
							'id'			=> "group$group[id]",
							'data-title'	=> $group['description']
						]
					);
				}
			}
			if (is_array($all_groups) && !empty($all_groups)) {
				foreach ($all_groups as $group) {
					if ($group['id'] == 3 || in_array($group['id'], $user_groups)) {
						continue;
					}
					$groups_list	.= h::{'li.uk-button-default'}(
						$group['title'],
						[
							'id'			=> "group$group[id]",
							'data-title'	=> $group['description']
						]
					);
				}
			}
			$Page->title(
				$L->user_groups($User->username($rc[3]))
			);
			$a->content(
				h::{'p.lead.cs-center'}(
					$L->user_groups(
						$User->username($rc[3])
					),
					[
						'data-title'	=> $L->user_groups_info
					]
				).
				h::{'table.cs-table-borderless tr td'}(
					[
						h::{'ul#cs-users-groups-list-selected'}($groups_selected),
						h::{'ul#cs-users-groups-list'}($groups_list)
					],
					[
						'style'	=> 'vertical-align: top;'
					]
				).
				h::{'input[type=hidden]'}([
					'name'	=> 'user[id]',
					'value'	=> $rc[3]
				]).
				h::{'input#cs-user-groups[type=hidden]'}([
					'name'	=> 'user[groups]'
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
	$users_db		= $User->db();
	$columns		= isset($_POST['columns']) && $_POST['columns'] ? explode(';', $_POST['columns']) : [
		'id', 'login', 'username', 'email'
	];
	$limit			= isset($_POST['search_limit'])	? (int)$_POST['search_limit']	: 20;
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
		$columns_list .= h::{'li.cs-pointer.uk-button.uk-margin-bottom'}(
			$column,
			[
				'class'	=> in_array($column, $columns) ? 'ui-selected uk-button-primary' : ''
			]
		);
	}
	unset($column);
	$columns		= array_intersect($search_columns, $columns);
	$search_column	= isset($_POST['search_column']) && in_array($_POST['search_column'], $search_columns) ? $_POST['search_column'] : '';
	/**
	 * Closures for constructing WHERE part of SQL query
	 */
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
	/**
	 * Applying (if necessary) filter
	 */
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
				$search_text_ = $users_db->s($search_text);
				$where = $where_func("`%%` $search_mode $search_text_");
				unset($search_text_);
				break;
			case 'IN':
			case 'NOT IN':
				$search_text_ = implode(
					", ",
					$users_db->s(
						_trim(
							explode(',', $search_text),
							"\n'"
						)
					)
				);
				$where = $where_func("`%%` $search_mode ($search_text_)");
				unset($search_text_);
				break;
		}
	}
	$results_count	= $users_db->qfs(
		"SELECT COUNT(`id`)
		FROM `[prefix]users`
		WHERE
			(
				$where
			) AND
			`status` != '-1'"
	);
	if ($results_count) {
		$from		= $start * $limit;
		$users_ids	= $users_db->qfas(
			"SELECT `id`
			FROM `[prefix]users`
			WHERE
				(
					$where
				) AND
				`status` != '-1'
			ORDER BY `id`
			LIMIT $from, $limit"
		);
		unset($from);
	}
	$users_list				= [];
	if (isset($users_ids) && is_array($users_ids)) {
		foreach ($users_ids as $id) {
			$groups			= (array)$User->get_groups($id);
			$buttons		= ($id != 1 && $id != 2 && !in_array(3, $groups) ?
				h::{'a.cs-button-compact'}(
					h::icon('pencil'),
					[
						'href'			=> "$a->action/edit_raw/$id",
						'data-title'	=> $L->edit_raw_user_data
					]
				) : ''
			).
			($id != 1 && $id != 2 && (!in_array(3, $groups) || !$Config->core['simple_admin_mode']) ?
				h::{'a.cs-button-compact'}(
					h::icon('edit'),
					[
						'href'			=> "$a->action/edit/$id",
						'data-title'	=> $L->{in_array(3, $groups) ? 'edit_bot_information' : 'edit_user_information'}
					]
				) : ''
			).
			($id != 1 && $id != 2 ?
				h::{'a.cs-button-compact'}(
					h::icon($User->get('status', $id) == 1 ? 'check-minus' : 'check'),
					[
						'href'			=> "$a->action/".($User->get('status', $id) == 1 ? 'deactivate' : 'activate')."/$id",
						'data-title'	=> $L->{($User->get('status', $id) == 1 ? 'de' : '').'activate_'.(in_array(3, $groups) ? 'bot' : 'user')}
					]
				) : ''
			).($id != 1 && $id != 2 && !in_array(3, $groups) ?
				h::{'a.cs-button-compact'}(
					h::icon('group'),
					[
						'href'			=> "$a->action/groups/$id",
						'data-title'	=> $L->edit_user_groups
					]
				) : ''
			).($id != 2  ?
				h::{'a.cs-button-compact'}(
					h::icon('key'),
					[
						'href'			=> "$a->action/permissions/$id",
						'data-title'	=> $L->{in_array(3, $groups) ? 'edit_bot_permissions' : 'edit_user_permissions'}
					]
				) : '-'
			);
			$user_data		= $User->get($columns, $id);
			if ($id == 2 && isset($user_data['password_hash'])) {
				$user_data['password_hash'] = '*****';
			}
			if (isset($user_data['reg_ip'])) {
				$user_data['reg_ip'] = hex2ip($user_data['reg_ip'], 10);
				if ($user_data['reg_ip'][1]) {
					$user_data['reg_ip'] = $user_data['reg_ip'][0].h::br().$user_data['reg_ip'][1];
				} else {
					$user_data['reg_ip'] = $user_data['reg_ip'][0];
				}
			}
			if (isset($user_data['last_ip'])) {
				$user_data['last_ip'] = hex2ip($user_data['last_ip'], 10);
				if ($user_data['last_ip'][1]) {
					$user_data['last_ip'] = $user_data['last_ip'][0].h::br().$user_data['last_ip'][1];
				} else {
					$user_data['last_ip'] = $user_data['last_ip'][0];
				}
			}
			if (in_array(1, $groups)) {
				$type = h::info('a');
			} elseif (in_array(2, $groups)) {
				$type = h::info('u');
			} elseif (in_array(3, $groups)) {
				$type = h::info('b');
			} else {
				$type = h::info('g');
			}
			$users_list[]	= array_values([$buttons, $type]+$user_data);
		}
	}
	unset($id, $buttons, $user_data, $users_ids);
	$a->content(
		h::{'ul.cs-tabs li'}(
			$L->search,
			h::info('show_columns')
		).
		h::div(
			h::div(
				h::select(
					[
						'in'		=> array_merge([$L->all_columns], $search_columns),
						'value'		=> array_merge([''], $search_columns)
					],
					[
						'selected'	=> $search_column ?: '',
						'name'		=> 'search_column'
					]
				).
				$L->search_mode.' '.
				h::select(
					$search_modes,
					[
						'selected'	=> $search_mode ?: 'LIKE',
						'name'		=> 'search_mode'
					]
				).
				h::{'input.uk-form-width-medium'}([
					'value'			=> $search_text,
					'name'			=> 'search_text',
					'placeholder'	=> $L->search_text
				]).
				$L->page.' '.
				h::{'input[type=number]'}([
					'value'	=> $start + 1,
					'min'	=> 1,
					'name'	=> 'search_start'
				]).
				$L->items.' '.
				h::{'input[type=number]'}([
					'value'	=> $limit,
					'min'	=> 1,
					'name'	=> 'search_limit'
				]),
				[
					'style'	=> 'text-align: left;'
				]
			).
			h::{'ul#cs-users-search-columns.uk-padding-remove'}($columns_list)
		).
		h::{'input#cs-users-search-selected-columns[name=columns][type=hidden]'}().
		h::hr().
		h::{'p.cs-left'}(
			h::{'button[type=submit]'}($L->search),
			$L->found_users($results_count).($results_count > $limit ? ' / '.$L->page_from($start+1, ceil($results_count / $limit)) : '')
		).
		h::{'table.cs-table.cs-center-all'}(
			h::{'thead tr th'}(
				array_merge([$L->action, ''], $columns)
			).
			h::{'tbody tr| td'}($users_list)
		).
		h::{'p.cs-left'}(
			$L->found_users($results_count).($results_count > $limit ? ' / '.$L->page_from($start+1, ceil($results_count / $limit)) : ''),
			h::{'a.cs-button'}(
				$L->add_user,
				[
					'href' => 'admin/System/users/users/add/0',
				]
			).
			h::{'a.cs-button'}(
				$L->add_bot,
				[
					'href' => 'admin/System/users/users/add_bot/0',
				]
			)
		)
	);
}