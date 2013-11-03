<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  System/profile/settings
 */
namespace	cs;
use			h;
$Config			= Config::instance();
$Index			= Index::instance();
$L				= Language::instance();
$Page			= Page::instance();
$User			= User::instance();
if (!$User->user()) {
	error_code(403);
	$Page->error();
	return;
}
$columns		= [
	'login',
	'username',
	'language',
	'timezone',
	'avatar'
];
if (isset($_POST['user']) && $_POST['edit_settings'] == 'save') {
	$user_data = &$_POST['user'];
	foreach ($user_data as $item => &$value) {
		if (in_array($item, $columns) && $item != 'data') {
			$value = xap($value, false);
		}
	}
	unset($item, $value);
	if (isset($user_data['login'])) {
		$user_data['login']	= mb_strtolower($user_data['login']);
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
	)) {
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
$Index->action	= path($L->profile).'/'.path($L->settings);
switch (isset($Config->route[2]) ? $Config->route[2] : '') {
	default:
		$Index->content(
			h::{'a.cs-button'}(
				$L->general,
				[
					'href'	=> "$Index->action/".path($L->general)
				]
			).
			h::{'a.cs-button'}(
				$L->change_password,
				[
					'href'	=> "$Index->action/".path($L->change_password)
				]
			)
		);
		Trigger::instance()->run('System/profile/settings');
	break;
	case 'general':
		$user_data						= $User->get($columns);
		unset($columns);
		$timezones						= get_timezones_list();
		$row							= function ($col1, $col2) {
			return	h::th($col1).
				h::td($col2);
		};
		$Index->form					= true;
		$Index->form_atributes['class']	= 'cs-center';
		$Index->apply_button			= false;
		$Index->cancel_button_back		= true;
		$Page->title($L->general);
		$Index->content(
			h::{'p.lead.cs-center'}(
				$L->general_settings
			).
			h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr'}(
				$row($L->login, h::input([
					'name'		=> 'user[login]',
					'value'		=> $user_data['login']
				])),

				$row($L->username, h::input([
					'name'	=> 'user[username]',
					'value'	=> $user_data['username']
				])),
				$row($L->language, h::select(
					[
						'in'		=> array_merge([$L->system_default], $Config->core['active_languages']),
						'value'		=> array_merge([''], $Config->core['active_languages'])
					],
					[
						'name'		=> 'user[language]',
						'selected'	=> $user_data['language'],
						'size'		=> 5
					]
				)),
				$row($L->timezone, h::select(
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
				$row($L->avatar, h::input([
					'name'		=> 'user[avatar]',
					'value'		=> $user_data['avatar']
				]))
			)
		);
	break;
	case 'change_password':
		$Index->form					= true;
		$Index->form_atributes['class']	= 'cs-center';
		$Index->buttons					= false;
		$Index->cancel_button_back		= true;
		$Page->title($L->password_changing);
		$Index->content(
			h::{'p.lead.cs-center'}(
				$L->password_changing
			).
			h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr'}(
				h::th(
					"$L->current_password ".h::{'icon#current_password'}('lock')
				).
				h::td(
					h::{'input.cs-profile-current-password[type=password]'}()
				),
				h::th(
					"$L->new_password ".h::{'icon#new_password'}('lock')
				).
				h::td(
					h::{'input.cs-profile-new-password[type=password]'}()
				)
			).
			h::{'button.cs-profile-change-password'}(
				$L->change_password
			)
		);
	break;
}