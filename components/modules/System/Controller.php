<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System;
use
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Mail,
	cs\Page,
	cs\Page\Meta,
	cs\Route,
	cs\User,
	h;
class Controller {
	static function profile () {
		$rc       = &Route::instance()->route;
		$subparts = file_get_json(__DIR__.'/index.json')[$rc[0]];
		$User     = User::instance();
		if (
			(
				!isset($rc[1]) && $User->user()
			) ||
			(
				isset($rc[1]) && !in_array($rc[1], $subparts)
			)
		) {
			if (isset($rc[1])) {
				$rc[2] = $rc[1];
			} else {
				$rc[2] = $User->login;
			}
			$rc[1] = $subparts[0];
		}
	}
	static function profile_info () {
		$L    = Language::instance();
		$Page = Page::instance();
		$User = User::instance();
		$rc   = Route::instance()->route;
		if (!isset($rc[1], $rc[2]) || !($id = $User->get_id(hash('sha224', $rc[2])))) {
			error_code(404);
			$Page->error();
			return;
		}
		$data = $User->get(
			[
				'username',
				'login',
				'reg_date',
				'status',
				'block_until'
			],
			$id
		);
		if ($data['status'] == User::STATUS_NOT_ACTIVATED) {
			error_code(404);
			$Page->error();
			return;
		} elseif ($data['status'] == User::STATUS_INACTIVE) {
			$Page->warning(
				h::tr(
					[
						$L->account_disabled
					]
				)
			);
			return;
		} elseif ($data['block_until'] > TIME) {
			$Page->warning(
				h::tr(
					[
						$L->account_temporarily_blocked
					]
				)
			);
		}
		$name = $data['username'] ? $data['username'].($data['username'] != $data['login'] ? ' aka '.$data['login'] : '') : $data['login'];
		$Page->title($L->profile_of_user($name));
		Meta::instance()
			->profile()
			->profile('username', $name)
			->image($User->avatar(256, $id));
		$Page->content(
			h::{'div[layout][horizontal]'}(
				h::{'div.cs-profile-avatar img'}(
					[
						'src'   => $User->avatar(128, $id),
						'alt'   => $name,
						'title' => $name
					]
				).
				h::{'div[flex]'}(
					h::h1(
						$L->profile_of_user($name)
					).
					h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
						[
							$data['reg_date']
								? [
								h::h2("$L->reg_date:"),
								h::h2($L->to_locale(date($L->reg_date_format, $data['reg_date'])))
							]
								: false
						]
					)
				)
			)
		);
		Event::instance()->fire(
			'System/profile/info',
			[
				'id' => $id
			]
		);
	}
	static function profile_registration_confirmation () {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$Route  = Route::instance();
		$User   = User::instance();
		if (_getcookie('reg_confirm')) {
			_setcookie('reg_confirm', '');
			$Page->title($L->reg_success_title);
			$Page->success($L->reg_success);
			return;
		} elseif (!$User->guest()) {
			$Page->title($L->you_are_already_registered_title);
			$Page->warning($L->you_are_already_registered);
			return;
		} elseif (!isset($Route->route[2])) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		$result = $User->registration_confirmation($Route->route[2]);
		if ($result === false) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		$body = $L->reg_success_mail_body(
			strstr($result['email'], '@', true),
			get_core_ml_text('name'),
			$Config->core_url().'/profile/settings',
			$User->get('login', $result['id']),
			$result['password']
		);
		if (Mail::instance()->send_to(
			$result['email'],
			$L->reg_success_mail(get_core_ml_text('name')),
			$body
		)
		) {
			_setcookie('reg_confirm', 1);
			_header("Location: {$Config->base_url()}/System/profile/registration_confirmation");
		} else {
			$User->registration_cancel();
			$Page->title($L->sending_reg_mail_error_title);
			$Page->warning($L->sending_reg_mail_error);
		}
	}
	static function profile_restore_password_confirmation () {
		$Config = Config::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$Route  = Route::instance();
		$User   = User::instance();
		if (_getcookie('restore_password_confirm')) {
			_setcookie('restore_password_confirm', '');
			$Page->title($L->restore_password_success_title);
			$Page->success($L->restore_password_success);
			return;
		} elseif (!$User->guest()) {
			$Page->title($L->you_are_already_registered_title);
			$Page->warning($L->you_are_already_registered);
			return;
		} elseif (!isset($Route->route[2])) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		$result = $User->restore_password_confirmation($Route->route[2]);
		if ($result === false) {
			$Page->title($L->invalid_confirmation_code);
			$Page->warning($L->invalid_confirmation_code);
			return;
		}
		if (Mail::instance()->send_to(
			$User->get('email', $result['id']),
			$L->restore_password_success_mail(get_core_ml_text('name')),
			$L->restore_password_success_mail_body(
				$User->username($result['id']),
				get_core_ml_text('name'),
				$Config->core_url().'/profile/settings',
				$User->get('login', $result['id']),
				$result['password']
			)
		)
		) {
			_setcookie('restore_password_confirm', 1);
			_header("Location: {$Config->base_url()}/System/profile/restore_password_confirmation");
		} else {
			$Page->title($L->sending_reg_mail_error_title);
			$Page->warning($L->sending_reg_mail_error);
		}
	}
	static function profile_settings () {
		$Config = Config::instance();
		$Index  = Index::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$Route  = Route::instance();
		$User   = User::instance();
		if (!$User->user()) {
			error_code(403);
			$Page->error();
			return;
		}
		$columns = [
			'login',
			'username',
			'language',
			'timezone',
			'avatar'
		];
		if (isset($_POST['user'], $_POST['save'])) {
			$user_data = &$_POST['user'];
			foreach ($user_data as $item => &$value) {
				if ($item != 'data' && in_array($item, $columns)) {
					$value = xap($value, false);
				}
			}
			unset($item, $value);
			if (isset($user_data['login'])) {
				$user_data['login'] = mb_strtolower($user_data['login']);
			}
			if (!(
				isset($user_data['login']) &&
				$user_data['login'] &&
				$user_data['login'] != $User->get('login') &&
				(
					(
						!filter_var($user_data['login'], FILTER_VALIDATE_EMAIL) &&
						$User->get_id(hash('sha224', $user_data['login'])) === false
					) ||
					$user_data['login'] == $User->get('email')
				)
			)
			) {
				if ($user_data['login'] != $User->get('login')) {
					$Page->warning($L->login_occupied_or_is_not_valid);
				}
				unset($user_data['login']);
			}
			$Index->save($User->set($user_data));
			unset($user_data);
		}
		$Page->title($L->my_profile);
		$Page->title($L->settings);
		$Index->action = path($L->profile).'/'.path($L->settings);
		switch (isset($Route->route[2]) ? $Route->route[2] : '') {
			default:
				$Index->content(
					h::p(
						h::{'a.uk-button'}(
							$L->general,
							[
								'href' => "$Index->action/".path($L->general)
							]
						).
						h::{'a.uk-button'}(
							$L->change_password,
							[
								'href' => "$Index->action/".path($L->change_password)
							]
						)
					)
				);
				Event::instance()->fire('System/profile/settings');
				break;
			case 'general':
				$user_data = $User->get($columns);
				unset($columns);
				$timezones   = get_timezones_list();
				$Index->form = true;
				$Index->form_attributes['class'] .= ' cs-center';
				$Index->apply_button       = false;
				$Index->cancel_button_back = true;
				$Page->title($L->general);
				$Index->content(
					h::{'h2.cs-center'}(
						$L->general_settings
					).
					h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
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
							$L->language,
							h::select(
								[
									'in'    => array_merge([$L->system_default], $Config->core['active_languages']),
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
							$L->avatar,
							h::input(
								[
									'name'  => 'user[avatar]',
									'value' => $user_data['avatar']
								]
							)
						]
					)
				);
				break;
			case 'change_password':
				$Index->form = true;
				$Index->form_attributes['class'] .= ' cs-center';
				$Index->buttons            = false;
				$Index->cancel_button_back = true;
				$Page->title($L->password_changing);
				$Index->content(
					h::{'h2.cs-center'}(
						$L->password_changing
					).
					h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
						[
							"$L->current_password ".h::{'icon#current_password.cs-pointer'}('lock'),
							h::{'input.cs-profile-current-password[type=password]'}()
						],
						[
							"$L->new_password ".h::{'icon#new_password.cs-pointer'}('lock'),
							h::{'input.cs-profile-new-password[type=password]'}()
						]
					).
					h::{'button.uk-button.cs-profile-change-password'}(
						$L->change_password
					)
				);
				break;
		}
	}
	function robots_txt () {
		interface_off();
		$text = file_get_contents(__DIR__.'/robots.txt');
		Event::instance()->fire(
			'System/robots.txt',
			[
				'text' => &$text
			]
		);
		$host = explode(
					'/',
					explode('//', Config::instance()->core_url(), 2)[1],
					2
				)[0];
		$text .= "Host: $host";
		Page::instance()->Content = $text;
	}
}
