<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Config,
	cs\Group,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Permission,
	cs\Route,
	cs\Text,
	cs\User,
	h;

trait users {
	static function users_general () {
		$Config = Config::instance();
		$L      = Language::instance();
		Index::instance()->content(
			static::vertical_table(
				static::core_input('session_expire', 'number', null, false, 1, false, $L->seconds),
				static::core_input('online_time', 'number', null, false, 1, false, $L->seconds),
				[
					h::info('sign_in_attempts_block_count'),
					h::{'input[type=number]'}(
						[
							'name'     => 'core[sign_in_attempts_block_count]',
							'value'    => $Config->core['sign_in_attempts_block_count'],
							'min'      => 0,
							'onChange' => "if ($(this).val() == 0) { $('.cs-sign-in-attempts-block-count').hide(); } else { $('.cs-sign-in-attempts-block-count').show(); }"
						]
					)
				],
				[
					static::core_input('sign_in_attempts_block_time', 'number', null, false, 1, false, $L->seconds),
					[
						'style' => $Config->core['sign_in_attempts_block_count'] == 0 ? 'display: none;' : '',
						'class' => 'cs-sign-in-attempts-block-count'
					]
				],
				static::core_input('remember_user_ip', 'radio'),
				static::core_input('password_min_length', 'number', null, false, 4),
				static::core_input('password_min_strength', 'number', null, false, 0, 7),
				[
					h::info('allow_user_registration'),
					h::radio(
						[
							'name'     => 'core[allow_user_registration]',
							'checked'  => $Config->core['allow_user_registration'],
							'value'    => [0, 1],
							'in'       => [$L->off, $L->on],
							'onchange' => [
								"$('.cs-allow-user-registration').hide();",
								"$('.cs-allow-user-registration').show();".
								"if (!$('.cs-allow-user-registration input[value=1]').prop('checked')) { $('.cs-require-registration-confirmation').hide(); }"
							]
						]
					)
				],
				[
					[
						h::info('require_registration_confirmation'),
						h::radio(
							[
								'name'     => 'core[require_registration_confirmation]',
								'checked'  => $Config->core['require_registration_confirmation'],
								'value'    => [0, 1],
								'in'       => [$L->off, $L->on],
								'onchange' => [
									"$('.cs-require-registration-confirmation').hide();",
									"$('.cs-require-registration-confirmation').show();"
								]
							]
						)
					],
					[
						'style' => $Config->core['allow_user_registration'] == 0 ? 'display: none;' : '',
						'class' => 'cs-allow-user-registration'
					]
				],
				[
					static::core_input('registration_confirmation_time', 'number', null, false, 1, false, $L->days),
					[
						'style' => $Config->core['allow_user_registration'] == 1 && $Config->core['require_registration_confirmation'] == 1 ? '' :
							'display: none;',
						'class' => 'cs-allow-user-registration cs-require-registration-confirmation'
					]
				],
				[
					static::core_input('auto_sign_in_after_registration', 'radio'),
					[
						'style' => $Config->core['allow_user_registration'] == 1 && $Config->core['require_registration_confirmation'] == 1 ? '' :
							'display: none;',
						'class' => 'cs-allow-user-registration cs-require-registration-confirmation'
					]
				],
				static::core_textarea('rules', 'SIMPLE_EDITOR')
			)
		);
	}
	static function users_groups () {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$Group  = Group::instance();
		$a      = Index::instance();
		$rc     = Route::instance()->route;
		if (isset($rc[2])) {
			switch ($rc[2]) {
				case 'add':
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$Page->title($L->adding_a_group);
					$a->content(
						h::{'h2.cs-center'}(
							$L->adding_a_group
						).
						static::horizontal_table(
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
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$group_data            = $Group->get($rc[3]);
					$Page->title(
						$L->editing_of_group($group_data['title'])
					);
					$a->content(
						h::{'h2.cs-center'}(
							$L->editing_of_group($group_data['title'])
						).
						static::horizontal_table(
							[
								'&nbsp;id&nbsp;',
								$L->group_name,
								$L->group_description,
								'data'
							],
							[
								$rc[3],
								h::input(
									[
										'name'  => 'group[title]',
										'value' => $group_data['title']
									]
								),
								h::input(
									[
										'name'  => 'group[description]',
										'value' => $group_data['description']
									]
								),
								h::{'textarea[name=group[data]]'}(
									$group_data['data']
								)
							]
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'group[id]',
								'value' => $rc[3]
							]
						)
					);
					break;
				case 'delete':
					if (!isset($rc[3])) {
						break;
					}
					$a->buttons            = false;
					$a->cancel_button_back = true;
					$group                 = $Group->get($rc[3]);
					$Page->title(
						$L->deletion_of_group($group['title'])
					);
					$a->content(
						h::{'h2.cs-center'}(
							$L->sure_delete_group($group['title'])
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'id',
								'value' => $rc[3]
							]
						).
						h::{'button.uk-button[type=submit]'}($L->yes)
					);
					$Page->warning($L->changing_settings_warning);
					break;
				case 'permissions':
					if (!isset($rc[3])) {
						break;
					}
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$permissions           = Permission::instance()->get_all();
					$group_permissions     = $Group->get_permissions($rc[3]);
					$tabs                  = [];
					$tabs_content          = '';
					$blocks                = [];
					foreach ($Config->components['blocks'] as $block) {
						$blocks[$block['index']] = $block['title'];
					}
					unset($block);
					foreach ($permissions as $group => $list) {
						$tabs[]  = h::a(
							$group,
							[
								'href' => '#permissions_group_'.strtr($group, '/', '_')
							]
						);
						$content = [];
						foreach ($list as $label => $id) {
							$content[] = h::cs_table_cell(
								$group == 'Block' ? Text::instance()->process($Config->module('System')->db('texts'), $blocks[$label]) : $label,
								h::radio(
									[
										'name'    => "permission[$id]",
										'checked' => isset($group_permissions[$id]) ? $group_permissions[$id] : -1,
										'value'   => [-1, 0, 1],
										'in'      => [$L->not_specified, $L->deny, $L->allow]
									]
								)
							);
						}
						if (count($list) % 2) {
							$content[] = h::cs_table_cell().h::cs_table_cell();
						}
						$count    = count($content);
						$content_ = [];
						for ($i = 0; $i < $count; $i += 2) {
							$content_[] = $content[$i].$content[$i + 1];
						}
						$tabs_content .= h::div(
							h::{'p.cs-left'}(
								h::{'button.uk-button.cs-permissions-invert'}($L->invert).
								h::{'button.uk-button.cs-permissions-deny-all'}($L->deny_all).
								h::{'button.uk-button.cs-permissions-allow-all'}($L->allow_all)
							).
							h::{'cs-table[right-left] cs-table-row'}($content_),
							[
								'id' => 'permissions_group_'.strtr($group, '/', '_')
							]
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
						h::{'input[type=hidden]'}(
							[
								'name'  => 'id',
								'value' => $rc[3]
							]
						)
					);
					break;
			}
			$a->content(
				h::{'input[type=hidden]'}(
					[
						'name'  => 'mode',
						'value' => $rc[2]
					]
				)
			);
		} else {
			$a->buttons  = false;
			$groups_ids  = $Group->get_all();
			$groups_list = [];
			foreach ($groups_ids as $id) {
				$id            = $id['id'];
				$group_data    = $Group->get($id);
				$groups_list[] = [
					h::{'a.uk-button.cs-button-compact'}(
						h::icon('pencil'),
						[
							'href'       => "$a->action/edit/$id",
							'data-title' => $L->edit_group_information
						]
					).
					($id != User::ADMIN_GROUP_ID && $id != User::USER_GROUP_ID && $id != User::BOT_GROUP_ID ? h::{'a.uk-button.cs-button-compact'}(
						h::icon('trash-o'),
						[
							'href'       => "$a->action/delete/$id",
							'data-title' => $L->delete
						]
					) : '').
					h::{'a.uk-button.cs-button-compact'}(
						h::icon('key'),
						[
							'href'       => "$a->action/permissions/$id",
							'data-title' => $L->edit_group_permissions
						]
					),
					$id,
					$group_data['title'],
					$group_data['description']
				];
			}
			unset($id, $group_data, $groups_ids);
			$a->content(
				static::list_center_table(
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
	}
	static function users_mail () {
		$Config = Config::instance();
		$L      = Language::instance();
		Index::instance()->content(
			static::vertical_table(
				[
					[
						h::info('smtp'),
						h::radio(
							[
								'name'    => 'core[smtp]',
								'checked' => $Config->core['smtp'],
								'value'   => [0, 1],
								'in'      => [$L->off, $L->on],
								'OnClick' => ["$('#smtp_form').parent().parent().hide();", "$('#smtp_form').parent().parent().show();"]
							]
						)
					],
					[
						[
							'',
							h::{'table#smtp_form tr'}(
								h::td(
									static::core_input('smtp_host')
								),
								h::td(
									static::core_input('smtp_port')
								),
								h::td(
									[
										h::info('smtp_secure'),
										h::radio(
											[
												'name'    => 'core[smtp_secure]',
												'checked' => $Config->core['smtp_secure'],
												'value'   => ['', 'ssl', 'tls'],
												'in'      => [$L->off, 'SSL', 'TLS']
											]
										)
									]
								),
								h::td(
									[
										$L->smtp_auth,
										h::radio(
											[
												'name'    => 'core[smtp_auth]',
												'checked' => $Config->core['smtp_auth'],
												'value'   => [0, 1],
												'in'      => [$L->off, $L->on],
												'OnClick' => ["$('#smtp_user, #smtp_password').hide();", "$('#smtp_user, #smtp_password').show();"]
											]
										)
									]
								),
								[
									h::td(
										static::core_input('smtp_user')
									),
									[
										'style' => (!$Config->core['smtp_auth'] ? 'display: none;' : '').' padding-left: 20px;',
										'id'    => 'smtp_user'
									]
								],
								[
									h::td(
										static::core_input('smtp_password')
									),
									[
										'style' => !$Config->core['smtp_auth'] ? 'display: none;' : '',
										'id'    => 'smtp_password'
									]
								]
							)
						],
						[
							'style' => !$Config->core['smtp'] ? 'display: none; ' : ''
						]
					],
					static::core_input('mail_from'),
					static::core_input('mail_from_name'),
					static::core_textarea('mail_signature', 'SIMPLE_EDITOR'),
					[
						'',
						h::{'td button.uk-button[onclick=cs.test_email_sending()]'}($L->test_email_sending)
					]
				]
			)
		);
	}
	static function users_permissions () {
		$Config     = Config::instance();
		$L          = Language::instance();
		$Page       = Page::instance();
		$Permission = Permission::instance();
		$a          = Index::instance();
		$rc         = Route::instance()->route;
		if (isset($rc[2])) {
			switch ($rc[2]) {
				case 'add':
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$Page->title($L->adding_permission);
					$a->content(
						h::{'h2.cs-center'}(
							$L->adding_permission
						).
						static::horizontal_table(
							[
								$L->group,
								$L->label
							],
							[
								h::{'input[name=permission[group]]'}(),
								h::{'input[name=permission[label]]'}()
							]
						)
					);
					break;
				case 'edit':
					if (!isset($rc[3])) {
						break;
					}
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$permission            = $Permission->get($rc[3]);
					$Page->title(
						$L->editing_permission("$permission[group]/$permission[label]")
					);
					$a->content(
						h::{'h2.cs-center'}(
							$L->editing_permission("$permission[group]/$permission[label]")
						).
						static::horizontal_table(
							[
								'&nbsp;id&nbsp;',
								$L->group,
								$L->label
							],
							[
								$rc[3],
								h::input(
									[
										'name'  => 'permission[group]',
										'value' => $permission['group']
									]
								),
								h::input(
									[
										'name'  => 'permission[label]',
										'value' => $permission['label']
									]
								)
							]
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'permission[id]',
								'value' => $rc[3]
							]
						)
					);
					$Page->warning($L->changing_settings_warning);
					break;
				case 'delete':
					if (!isset($rc[3])) {
						break;
					}
					$a->buttons            = false;
					$a->cancel_button_back = true;
					$permission            = $Permission->get($rc[3]);
					$Page->title(
						$L->deletion_of_permission("$permission[group]/$permission[label]")
					);
					$a->content(
						h::{'h2.cs-center'}(
							$L->sure_delete_permission("$permission[group]/$permission[label]")
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'id',
								'value' => $rc[3]
							]
						).
						h::{'button.uk-button[type=submit]'}($L->yes)
					);
					$Page->warning($L->changing_settings_warning);
					break;
			}
			$a->content(
				h::{'input[type=hidden]'}(
					[
						'name'  => 'mode',
						'value' => $rc[2]
					]
				)
			);
		} else {
			$a->buttons       = false;
			$permissions      = $Permission->get_all();
			$permissions_list = [];
			$count            = 0;
			$blocks           = [];
			foreach ($Config->components['blocks'] as $block) {
				$blocks[$block['index']] = $block['title'];
			}
			unset($block);
			foreach ($permissions as $group => $list) {
				foreach ($list as $label => $id) {
					++$count;
					$permissions_list[] = [
						h::{'a.uk-button.cs-button-compact'}(
							h::icon('pencil'),
							[
								'href'       => "$a->action/edit/$id",
								'data-title' => $L->edit
							]
						).
						h::{'a.uk-button.cs-button-compact'}(
							h::icon('trash-o'),
							[
								'href'       => "$a->action/delete/$id",
								'data-title' => $L->delete
							]
						),
						$id,
						h::span($group),
						h::span(
							$label,
							[
								'data-title' => $group == 'Block' ? Text::instance()->process($Config->module('System')->db('texts'), $blocks[$label]) : false
							]
						)
					];
				}
			}
			$a->content(
				static::list_center_table(
					[
						$L->action,
						'id',
						$L->group,
						$L->label
					],
					$permissions_list
				).
				h::{'p.cs-left a.uk-button'}(
					$L->add_permission,
					[
						'href' => "admin/System/$rc[0]/$rc[1]/add"
					]
				)
			);
		}
	}
	static function users_security () {
		$Config = Config::instance();
		$L      = Language::instance();
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		Index::instance()->content(
			static::vertical_table(
				[
					[
						h::info('key_expire'),
						h::{'input[type=number]'}(
							[
								'name'  => 'core[key_expire]',
								'value' => $Config->core['key_expire'],
								'min'   => 1
							]
						).
						$L->seconds
					],
					[
						h::info('ip_black_list'),
						h::textarea(
							$Config->core['ip_black_list'],
							[
								'name' => 'core[ip_black_list]'
							]
						)
					],
					[
						h::info('ip_admin_list_only'),
						h::radio(
							[
								'name'    => 'core[ip_admin_list_only]',
								'checked' => $Config->core['ip_admin_list_only'],
								'value'   => [0, 1],
								'in'      => [$L->off, $L->on]
							]
						)
					],
					[
						h::info('ip_admin_list'),
						h::textarea(
							$Config->core['ip_admin_list'],
							[
								'name' => 'core[ip_admin_list]'
							]
						).
						h::br().
						$L->current_ip.': '.h::b($_SERVER->ip)
					]
				]
			)
		);
	}
	static function users_users () {
		$Config         = Config::instance();
		$L              = Language::instance();
		$Page           = Page::instance();
		$User           = User::instance();
		$a              = Index::instance();
		$rc             = $Config->route;
		$search_columns = $User->get_users_columns();
		if (isset($rc[2], $rc[3])) {
			$is_bot = in_array(3, (array)$User->get_groups($rc[3]));
			switch ($rc[2]) {
				case 'add':
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$Page->title($L->adding_a_user);
					$a->content(
						h::{'h2.cs-center'}(
							$L->adding_a_user
						).
						h::{'p.cs-center input'}(
							[
								'name'        => 'email',
								'placeholder' => $L->email
							]
						)
					);
					break;
				case 'add_bot':
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$Page->title($L->adding_a_bot);
					$a->content(
						h::{'h2.cs-center'}(
							$L->adding_a_bot
						).
						static::vertical_table(
							[
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
							]
						)
					);
					break;
				case 'edit_raw':
					if ($is_bot || $rc[3] == User::GUEST_ID || $rc[3] == User::ROOT_ID) {
						break;
					}
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$content               = $content_ = '';
					$user_data             = $User->get($search_columns, $rc[3]);
					foreach ($search_columns as $i => $column) {
						$content_ .= h::cs_table_cell(
							$column,
							$column == 'data'
								? h::textarea(
								$user_data[$column],
								[
									'name' => "user[$column]"
								]
							)
								: h::input(
								[
									'name'  => "user[$column]",
									'value' => $user_data[$column],
									$column == 'id' ? 'readonly' : false
								]
							)
						);
						if ($i % 2) {
							$content .= h::cs_table_row(
								$content_
							);
							$content_ = '';
						}
					}
					if ($content_ != '') {
						$content .= h::cs_table_row(
							$content_
						);
					}
					unset($i, $column, $content_);
					$Page->title(
						$L->editing_raw_data_of_user($User->username($rc[3]))
					);
					$a->content(
						h::{'h2.cs-center'}(
							$L->editing_raw_data_of_user(
								$User->username($rc[3])
							)
						).
						h::{'cs-table[right-left]'}($content)
					);
					break;
				case 'edit':
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					if (!$is_bot) {
						if ($rc[3] == User::GUEST_ID || $rc[3] == User::ROOT_ID) {
							break;
						}
						$user_data = $User->get(
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
								'last_sign_in',
								'last_ip',
								'last_online',
								'avatar'
							],
							$rc[3]
						);
						$timezones = get_timezones_list();
						$reg_ip    = hex2ip($user_data['reg_ip'], 10);
						$last_ip   = hex2ip($user_data['last_ip'], 10);
						$Page->title(
							$L->editing_of_user_information($User->username($rc[3]))
						);
						$a->content(
							h::{'h2.cs-center'}(
								$L->editing_of_user_information(
									$User->username($rc[3])
								)
							).
							static::vertical_table(
									[
										'id',
										$rc[3]
									],
									[
										$L->registration_date,
										$user_data['reg_date'] ? date($L->_date, $user_data['reg_date']) : $L->undefined
									],
									[
										$L->registration_ip,
										$reg_ip[0] ? $reg_ip[0].($reg_ip[1] ? h::br().$reg_ip[1] : '') : $L->undefined
									],
									[
										$L->last_sign_in,
										$user_data['last_sign_in'] ? date($L->_datetime, $user_data['last_sign_in']) : $L->undefined
									],
									[
										$L->last_ip,
										$last_ip[0] ? $last_ip[0].($last_ip[1] ? h::br().$last_ip[1] : '') : $L->undefined
									],
									[
										$L->last_online,
										$user_data['last_online'] ? date($L->_datetime, $user_data['last_online']) : $L->undefined
									],
									[
										$L->login,
										h::input(
											[
												'name'  => 'user[login]',
												'value' => $user_data['login']
											]
										)
									],
									[
										$L->username,
										h::input(
											[
												'name'  => 'user[username]',
												'value' => $user_data['username']
											]
										)
									],
									[
										$L->email,
										h::input(
											[
												'name'  => 'user[email]',
												'value' => $user_data['email']
											]
										)
									],
									[
										$L->password_only_for_changing.h::{'icon.cs-show-password.cs-pointer'}('lock'),
										h::{'input[type=password]'}(
											[
												'name'  => 'user[password]',
												'value' => ''
											]
										)
									],
									[
										$L->language,
										h::select(
											[
												'in'    => array_merge(["$L->system_default ({$Config->core['language']})"], $Config->core['active_languages']),
												'value' => array_merge([''], $Config->core['active_languages'])
											],
											[
												'name'     => 'user[language]',
												'selected' => $user_data['language'],
												'size'     => 5
											]
										)
									],
									[
										$L->timezone,
										h::select(
											[
												'in'    => array_merge(["$L->system_default ({$Config->core['timezone']})"], array_keys($timezones)),
												'value' => array_merge([''], array_values($timezones))
											],
											[
												'name'     => 'user[timezone]',
												'selected' => $user_data['timezone'],
												'size'     => 5
											]
										)
									],
									[
										$L->status,
										h::radio(
											[
												'name'    => 'user[status]',
												'checked' => $user_data['status'],
												'value'   => [User::STATUS_NOT_ACTIVATED, User::STATUS_INACTIVE, User::STATUS_ACTIVE],
												'in'      => [$L->is_not_activated, $L->inactive, $L->active]
											]
										)
									],
									[
										h::info('block_until'),
										h::{'input[type=datetime-local]'}(
											[
												'name'  => 'user[block_until]',
												'value' => date('Y-m-d\TH:i', $user_data['block_until'] ?: TIME)
											]
										)
									],
									[
										$L->avatar,
										h::input(
											[
												'name'  => 'user[avatar]',
												'value' => $user_data['avatar']
											]
										)
									]
							).
							h::{'input[type=hidden]'}(
								[
									'name'  => 'user[id]',
									'value' => $rc[3]
								]
							)
						);
					} else {
						$bot_data = $User->get(
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
							h::{'h2.cs-center'}(
								$L->editing_of_bot_information(
									$bot_data['username']
								)
							).
							static::vertical_table(
								[
									[
										$L->bot_name,
										h::input(
											[
												'name'  => 'bot[name]',
												'value' => $bot_data['username']
											]
										)
									],
									[
										h::info('bot_user_agent'),
										h::input(
											[
												'name'  => 'bot[user_agent]',
												'value' => $bot_data['login']
											]
										)
									],
									[
										h::info('bot_ip'),
										h::input(
											[
												'name'  => 'bot[ip]',
												'value' => $bot_data['email']
											]
										)
									]
								]
							).
							h::{'input[type=hidden]'}(
								[
									'name'  => 'bot[id]',
									'value' => $rc[3]
								]
							)
						);
					}
					break;
				case 'deactivate':
					if ($rc[3] == User::GUEST_ID || $rc[3] == User::ROOT_ID) {
						break;
					}
					$a->buttons            = false;
					$a->cancel_button_back = true;
					$user_data             = $User->get(['login', 'username'], $rc[3]);
					$a->content(
						h::{'p.cs-center-all'}(
							$L->{$is_bot ? 'sure_deactivate_bot' : 'sure_deactivate_user'}($user_data['username'] ?: $user_data['login'])
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'id',
								'value' => $rc[3]
							]
						).
						h::{'button.uk-button[type=submit]'}($L->yes)
					);
					break;
				case 'activate':
					if ($rc[3] == User::GUEST_ID || $rc[3] == User::ROOT_ID) {
						break;
					}
					$a->buttons            = false;
					$a->cancel_button_back = true;
					$user_data             = $User->get(['login', 'username'], $rc[3]);
					$a->content(
						h::{'p.cs-center-all'}(
							$L->{$is_bot ? 'sure_activate_bot' : 'sure_activate_user'}($user_data['username'] ?: $user_data['login'])
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'id',
								'value' => $rc[3]
							]
						).
						h::{'button.uk-button[type=submit]'}($L->yes)
					);
					break;
				case 'permissions':
					if (!isset($rc[3]) || $rc[3] == User::ROOT_ID) {
						break;
					}
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$permissions           = Permission::instance()->get_all();
					$user_permissions      = $User->get_permissions($rc[3]);
					$tabs                  = [];
					$tabs_content          = '';
					$blocks                = [];
					foreach ($Config->components['blocks'] as $block) {
						$blocks[$block['index']] = $block['title'];
					}
					unset($block);
					foreach ($permissions as $group => $list) {
						$tabs[]  = h::a(
							$group,
							[
								'href' => '#permissions_group_'.strtr($group, '/', '_')
							]
						);
						$content = [];
						foreach ($list as $label => $id) {
							$content[] = h::cs_table_cell(
								$group != 'Block' ? $label : Text::instance()->process($Config->module('System')->db('texts'), $blocks[$label]),
								h::radio(
									[
										'name'    => "permission[$id]",
										'checked' => isset($user_permissions[$id]) ? $user_permissions[$id] : -1,
										'value'   => [-1, 0, 1],
										'in'      => [
											$L->inherited.' ('.(isset($user_permissions[$id]) && !$user_permissions[$id] ? '-' : '+').')',
											$L->deny,
											$L->allow
										]
									]
								)
							);
						}
						if (count($list) % 2) {
							$content[] = h::cs_table_cell().h::cs_table_cell();
						}
						$count    = count($content);
						$content_ = [];
						for ($i = 0; $i < $count; $i += 2) {
							$content_[] = $content[$i].$content[$i + 1];
						}
						$tabs_content .= h::div(
							h::{'p.cs-left'}(
								h::{'button.uk-button.cs-permissions-invert'}($L->invert).
								h::{'button.uk-button.cs-permissions-deny-all'}($L->deny_all).
								h::{'button.uk-button.cs-permissions-allow-all'}($L->allow_all)
							).
							h::{'cs-table[right-left] cs-table-row'}($content_),
							[
								'id' => 'permissions_group_'.strtr($group, '/', '_')
							]
						);
					}
					unset($content, $content_, $count, $i, $permissions, $group, $list, $label, $id, $blocks);
					$Page->title(
						$L->{$is_bot ? 'permissions_for_bot' : 'permissions_for_user'}(
							$User->username($rc[3])
						)
					);
					$a->content(
						h::{'h2.cs-center'}(
							$L->{$is_bot ? 'permissions_for_bot' : 'permissions_for_user'}(
								$User->username($rc[3])
							)
						).
						h::{'ul.cs-tabs li'}($tabs).
						h::div($tabs_content).
						h::br().
						h::{'input[type=hidden]'}(
							[
								'name'  => 'id',
								'value' => $rc[3]
							]
						)
					);
					break;
				case 'groups':
					if ($is_bot || !isset($rc[3]) || $rc[3] == User::ROOT_ID) {
						break;
					}
					$a->apply_button       = false;
					$a->cancel_button_back = true;
					$Group                 = Group::instance();
					$user_groups           = array_reverse($User->get_groups($rc[3]));
					$all_groups            = $Group->get_all();
					$groups_selected       = h::{'li.uk-button-primary'}(
						$L->selected_groups
					);
					$groups_list           = h::{'li.uk-button-primary'}(
						$L->other_groups
					);
					if (is_array($user_groups) && !empty($user_groups)) {
						foreach ($user_groups as $group) {
							$group = $Group->get($group);
							$groups_selected .= h::{'li.uk-button-success'}(
								$group['title'],
								[
									'data-id'    => $group['id'],
									'data-title' => $group['description']
								]
							);
						}
					}
					if (is_array($all_groups) && !empty($all_groups)) {
						foreach ($all_groups as $group) {
							if ($group['id'] == User::BOT_GROUP_ID || in_array($group['id'], $user_groups)) {
								continue;
							}
							$groups_list .= h::{'li.uk-button-default'}(
								$group['title'],
								[
									'data-id'    => $group['id'],
									'data-title' => $group['description']
								]
							);
						}
					}
					$Page->title(
						$L->user_groups($User->username($rc[3]))
					);
					$a->content(
						h::{'h2.cs-center'}(
							$L->user_groups(
								$User->username($rc[3])
							),
							[
								'data-title' => $L->user_groups_info
							]
						).
						h::{'div[layout][horizontal]'}(
							h::{'ul#cs-users-groups-list-selected[flex]'}($groups_selected).
							h::{'ul#cs-users-groups-list[flex]'}($groups_list)
						).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'user[id]',
								'value' => $rc[3]
							]
						).
						h::{'input#cs-user-groups[type=hidden]'}(
							[
								'name' => 'user[groups]'
							]
						)
					);
					break;
			}
			$a->content(
				h::{'input[type=hidden]'}(
					[
						'name'  => 'mode',
						'value' => $rc[2]
					]
				)
			);
		} else {
			$a->buttons   = false;
			$users_db     = $User->db();
			$columns      = isset($_POST['columns']) && $_POST['columns'] ? explode(';', $_POST['columns']) : [
				'id',
				'login',
				'username',
				'email'
			];
			$limit        = isset($_POST['search_limit']) ? (int)$_POST['search_limit'] : 20;
			$page         = isset($_POST['page']) ? (int)$_POST['page'] : 1;
			$search_text  = isset($_POST['search_text']) ? $_POST['search_text'] : '';
			$columns_list = '';
			$search_modes = [
				'=',
				'!=',
				'>',
				'<',
				'>=',
				'<=',
				'LIKE',
				'NOT LIKE',
				'IN',
				'NOT IN',
				'IS NULL',
				'IS NOT NULL',
				'REGEXP',
				'NOT REGEXP'
			];
			$search_mode  = isset($_POST['search_mode']) && in_array($_POST['search_mode'], $search_modes) ? $_POST['search_mode'] : '';
			foreach ($search_columns as $column) {
				$columns_list .= h::{'li.cs-pointer.uk-button.uk-margin-bottom'}(
					$column,
					[
						'class' => in_array($column, $columns) ? 'uk-button-primary' : ''
					]
				);
			}
			unset($column);
			$columns       = array_intersect($search_columns, $columns);
			$search_column = isset($_POST['search_column']) && in_array($_POST['search_column'], $search_columns) ? $_POST['search_column'] : '';
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
						$where        = $where_func("`%%` $search_mode $search_text_");
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
						$where        = $where_func("`%%` $search_mode ($search_text_)");
						unset($search_text_);
						break;
				}
			}
			$results_count = $users_db->qfs(
				[
					"SELECT COUNT(`id`)
					FROM `[prefix]users`
					WHERE
						(
							$where
						) AND
						`status` != '%s'",
					User::STATUS_NOT_ACTIVATED
				]
			);
			if ($results_count) {
				$from      = ($page - 1) * $limit;
				$users_ids = $users_db->qfas(
					[
						"SELECT `id`
						FROM `[prefix]users`
						WHERE
							(
								$where
							) AND
							`status` != '%s'
						ORDER BY `id`
						LIMIT $from, $limit",
						User::STATUS_NOT_ACTIVATED
					]
				);
				unset($from);
			}
			$users_list = [];
			if (isset($users_ids) && is_array($users_ids)) {
				foreach ($users_ids as $id) {
					$is_guest  = $id == User::GUEST_ID;
					$is_root   = $id == User::ROOT_ID;
					$groups    = (array)$User->get_groups($id);
					$is_bot    = in_array(User::BOT_GROUP_ID, $groups);
					$status    = $User->get('status', $id);
					$is_active = $status == User::STATUS_ACTIVE;
					$buttons   = (!$is_guest && !$is_root && !$is_bot ?
							h::{'a.uk-button.cs-button-compact'}(
								h::icon('pencil'),
								[
									'href'       => "$a->action/edit_raw/$id",
									'data-title' => $L->edit_raw_user_data
								]
							) : ''
								 ).
								 (!$is_guest && !$is_root && (!$is_bot || !$Config->core['simple_admin_mode']) ?
									 h::{'a.uk-button.cs-button-compact'}(
										 h::icon('sliders'),
										 [
											 'href'       => "$a->action/edit/$id",
											 'data-title' => $L->{$is_bot ? 'edit_bot_information' : 'edit_user_information'}
										 ]
									 ) : ''
								 ).
								 (!$is_guest && !$is_root ?
									 h::{'a.uk-button.cs-button-compact'}(
										 h::icon($is_active ? 'minus' : 'check'),
										 [
											 'href'       => "$a->action/".($is_active ? 'deactivate' : 'activate')."/$id",
											 'data-title' => $L->{($is_active ? 'de' : '').'activate_'.($is_bot ? 'bot' : 'user')}
										 ]
									 ) : ''
								 ).
								 (!$is_guest && !$is_root && !$is_bot ?
									 h::{'a.uk-button.cs-button-compact'}(
										 h::icon('group'),
										 [
											 'href'       => "$a->action/groups/$id",
											 'data-title' => $L->edit_user_groups
										 ]
									 ) : ''
								 ).
								 (!$is_root ?
									 h::{'a.uk-button.cs-button-compact'}(
										 h::icon('key'),
										 [
											 'href'       => "$a->action/permissions/$id",
											 'data-title' => $L->{$is_bot ? 'edit_bot_permissions' : 'edit_user_permissions'}
										 ]
									 ) : '-'
								 );
					$user_data = $User->get($columns, $id);
					if ($is_root && isset($user_data['password_hash'])) {
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
					if (in_array(User::ADMIN_GROUP_ID, $groups)) {
						$type = h::info('a');
					} elseif (in_array(User::USER_GROUP_ID, $groups)) {
						$type = h::info('u');
					} elseif ($is_bot) {
						$type = h::info('b');
					} else {
						$type = h::info('g');
					}
					$users_list[] = [
						array_values([$buttons, $type] + $user_data),
						[
							'class' => $is_active ? 'uk-alert-success' : ($status == User::STATUS_INACTIVE ? 'uk-alert-warning' : false)
						]
					];
				}
			}
			unset($id, $buttons, $user_data, $users_ids, $is_guest, $is_root, $is_bot);
			$total_pages = ceil($results_count / $limit);
			$a->content(
				h::{'ul.cs-tabs li'}(
					$L->search,
					h::info('show_columns')
				).
				h::div(
					h::div(
						h::select(
							[
								'in'    => array_merge([$L->all_columns], $search_columns),
								'value' => array_merge([''], $search_columns)
							],
							[
								'selected' => $search_column ?: '',
								'name'     => 'search_column'
							]
						).
						$L->search_mode.' '.
						h::select(
							$search_modes,
							[
								'selected' => $search_mode ?: 'LIKE',
								'name'     => 'search_mode'
							]
						).
						h::{'input.uk-form-width-medium'}(
							[
								'value'       => $search_text,
								'name'        => 'search_text',
								'placeholder' => $L->search_text
							]
						).
						$L->items.' '.
						h::{'input[type=number]'}(
							[
								'value' => $limit,
								'min'   => 1,
								'name'  => 'search_limit'
							]
						),
						[
							'style' => 'text-align: left;'
						]
					).
					h::{'ul#cs-users-search-columns.uk-padding-remove'}($columns_list)
				).
				h::{'input#cs-users-search-selected-columns[name=columns][type=hidden]'}().
				h::hr().
				h::{'p.cs-left'}(
					h::{'button.uk-button[type=submit]'}($L->search),
					pages_buttons($page, $total_pages)
				).
				static::list_center_table(
					array_merge([$L->action, ''], $columns),
					$users_list
				).
				h::{'p.cs-left'}(
					pages_buttons($page, $total_pages),
					h::{'a.uk-button'}(
						$L->add_user,
						[
							'href' => 'admin/System/users/users/add/0'
						]
					).
					h::{'a.uk-button'}(
						$L->add_bot,
						[
							'href' => 'admin/System/users/users/add_bot/0'
						]
					)
				)
			);
		}
	}
}
