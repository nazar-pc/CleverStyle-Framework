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
	cs\Route,
	cs\User,
	h;

trait users {
	static function users_general () {
		$Config              = Config::instance();
		$Index               = Index::instance();
		$L                   = Language::instance();
		$Index->apply_button = true;
		$Index->content(
			static::vertical_table(
				static::core_input('session_expire', 'number', null, false, 1, false, $L->seconds),
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
		$a       = Index::instance();
		$a->form = false;
		$a->content(
			h::cs_system_admin_groups_list()
		);
	}
	static function users_mail () {
		$Config              = Config::instance();
		$Index               = Index::instance();
		$L                   = Language::instance();
		$Index->apply_button = true;
		$Index->content(
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
								'OnClick' => ["$('#smtp_form').parent().parent().children().hide();", "$('#smtp_form').parent().parent().children().show();"]
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
		$a       = Index::instance();
		$a->form = false;
		$a->content(
			h::cs_system_admin_permissions_list()
		);
	}
	static function users_security () {
		$Config = Config::instance();
		$Index  = Index::instance();
		$L      = Language::instance();
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		$Index->apply_button = true;
		$Index->content(
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
		$L              = Language::instance();
		$Page           = Page::instance();
		$User           = User::instance();
		$a              = Index::instance();
		$rc             = Route::instance()->route;
		$search_columns = $User->get_users_columns();
		if (isset($rc[2], $rc[3])) {
			$is_bot = in_array(3, (array)$User->get_groups($rc[3]));
			switch ($rc[2]) {
				case 'groups':
					if ($is_bot || !isset($rc[3]) || $rc[3] == User::ROOT_ID) {
						break;
					}
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
					$groups    = (array)$User->get_groups($id);
					$user_data = $User->get(
						array_unique(array_merge($columns, ['status'])),
						$id
					);
					if ($id == User::ROOT_ID && isset($user_data['password_hash'])) {
						$user_data['password_hash'] = '*****';
					}
					if (isset($user_data['reg_ip'])) {
						$user_data['reg_ip'] = hex2ip($user_data['reg_ip'], 10);
					}
					$users_list[] =
						$user_data +
						[
							'is_user'  => in_array(User::USER_GROUP_ID, $groups),
							'is_bot'   => in_array(User::BOT_GROUP_ID, $groups),
							'is_admin' => in_array(User::ADMIN_GROUP_ID, $groups),
							'username' => $User->username($id)
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
				h::{'cs-system-admin-users-list script[type=application/json]'}(
					json_encode(
						[
							'columns' => array_values($columns),
							'users'   => $users_list
						],
						JSON_UNESCAPED_UNICODE
					)
				).
				h::{'p.cs-left'}(
					pages_buttons($page, $total_pages)
				)
			);
		}
	}
}
