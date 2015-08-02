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
			$a->form = false;
			$a->content(
				h::{'cs-system-admin-users-list'}()
			);
		}
	}
}
