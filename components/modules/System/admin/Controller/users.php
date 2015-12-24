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
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_users_general()
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
					static::core_textarea('mail_signature', 'cs-editor-simple'),
					[
						'',
						h::{'td button[is=cs-button][onclick=cs.test_email_sending()]'}($L->test_email_sending)
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
						h::{'input[is=cs-input-text][type=number]'}(
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
						h::{'textarea[is=cs-textarea][autosize]'}(
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
						h::{'textarea[is=cs-textarea][autosize]'}(
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
		$L    = Language::instance();
		$Page = Page::instance();
		$User = User::instance();
		$a    = Index::instance();
		$rc   = Route::instance()->route;
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
					$groups_selected       = h::{'li.cs-block-primary.cs-text.primary'}(
						$L->selected_groups
					);
					$groups_list           = h::{'li.cs-block-primary.cs-text.primary'}(
						$L->other_groups
					);
					if (is_array($user_groups) && !empty($user_groups)) {
						foreach ($user_groups as $group) {
							$group = $Group->get($group);
							$groups_selected .= h::{'li.cs-block-success.cs-text-success'}(
								$group['title'],
								[
									'data-id' => $group['id'],
									'tooltip' => $group['description']
								]
							);
						}
					}
					if (is_array($all_groups) && !empty($all_groups)) {
						foreach ($all_groups as $group) {
							if ($group['id'] == User::BOT_GROUP_ID || in_array($group['id'], $user_groups)) {
								continue;
							}
							$groups_list .= h::{'li.cs-block-warning.cs-text-warning'}(
								$group['title'],
								[
									'data-id' => $group['id'],
									'tooltip' => $group['description']
								]
							);
						}
					}
					$Page->title(
						$L->user_groups($User->username($rc[3]))
					);
					$a->content(
						h::{'h2.cs-text-center'}(
							$L->user_groups(
								$User->username($rc[3])
							),
							[
								'tooltip' => $L->user_groups_info
							]
						).
						h::{'div'}(
							h::{'ul#cs-users-groups-list-selected'}($groups_selected).
							h::{'ul#cs-users-groups-list'}($groups_list)
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
			$a->form = false;
			$a->content(
				h::{'cs-system-admin-users-list'}()
			);
		}
	}
}
