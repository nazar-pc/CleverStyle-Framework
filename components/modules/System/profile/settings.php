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
global $Core, $Config, $L, $User, $Page, $Index;
if (!$User->user()) {
	define('ERROR_CODE', 403);
	$Page->error();
	return;
}
$columns		= [
	'login',
	'username',
	'language',
	'theme',
	'timezone',
	'gender',
	'birthday',
	'avatar',
	'website',
	'skype',
	'about'
];
if (isset($_POST['user']) && $_POST['edit_settings'] == 'save') {
	$user_data = &$_POST['user'];
	foreach ($user_data as $item => &$value) {
		if (in_array($item, $columns) && $item != 'data') {
			$value = xap($value, false);
		}
	}
	unset($item, $value);
	if ($_POST['user']['birthday'] < TIME) {
		$birthday				= $user_data['birthday'];
		$birthday				= explode('-', $birthday);
		$user_data['birthday']	= mktime(
			0,
			0,
			0,
			$birthday[1],
			$birthday[2],
			$birthday[0]
		);
		unset($birthday);
	} else {
		$user_data['block_until']	= 0;
	}
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
	if ($user_data['theme']) {
		$theme = _json_decode($user_data['theme']);
		if (!(
			in_array($theme['theme'], $Config->core['active_themes']) &&
			in_array($theme['color_scheme'], $Config->core['color_schemes'][$theme['theme']])
		)) {
			unset($user_data['theme']);
		}
		unset($theme);
	} else {
		unset($user_data['theme']);
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
					'href'	=> $Index->action.'/'.path($L->general)
				]
			).
			h::{'a.cs-button'}(
				$L->change_password,
				[
					'href'	=> $Index->action.'/'.path($L->change_password)
				]
			)
		);
		$Core->run_trigger('System/profile/settings');
	break;
	case 'general':
		$user_data					= $User->get($columns);
		unset($columns);
		$timezones					= get_timezones_list();
		$row						= function ($col1, $col2) {
			return	h::{'th.ui-widget-header.ui-corner-all'}($col1).
				h::{'td.ui-widget-content.ui-corner-all'}($col2);
		};
		$themes						= [
			$L->system_default.' ('.$Config->core['theme'].' - '.$Config->core['color_scheme'].')' => ''
		];
		foreach ($Config->core['active_themes'] as $theme) {
			foreach ($Config->core['color_schemes'][$theme] as $color_scheme) {
				$themes[$theme.' - '.$color_scheme] = _json_encode([
					'theme'			=> $theme,
					'color_scheme'	=> $color_scheme
				]);
			}
		}
		unset($theme, $color_scheme);
		$Index->form				= true;
		$Index->apply_button		= false;
		$Index->cancel_button_back	= true;
		$Page->title($L->general);
		$Index->content(
			h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
				$L->general_settings
			).
			h::{'table#users_edit.cs-fullwidth-table.cs-center-all tr'}(
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
						'in'		=> array_merge([$L->system_default.' ('.$Config->core['language'].')'], $Config->core['active_languages']),
						'value'		=> array_merge([''], $Config->core['active_languages'])
					],
					[
						'name'		=> 'user[language]',
						'selected'	=> $user_data['language'],
						'size'		=> 5
					]
				)),
				$row($L->theme, h::select(
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
				$row($L->timezone, h::select(
					[
						'in'		=> array_merge([$L->system_default.' ('.$Config->core['timezone'].')'], array_keys($timezones)),
						'value'		=> array_merge([''], array_values($timezones))
					],
					[
						'name'		=> 'user[timezone]',
						'selected'	=> $user_data['timezone'],
						'size'		=> 5
					]
				)),
				$row($L->gender, h::{'input[type=radio]'}([
					'name'		=> 'user[gender]',
					'checked'	=> $user_data['gender'],
					'value'		=> [-1, 0, 1],
					'in'		=> [$L->undefined, $L->male, $L->female]
				])),
				$row(h::info('birthday'), h::{'input'}([
					'name'			=> 'user[birthday]',
					'value'			=> $user_data['birthday'] ? date('Y-m-d', $user_data['birthday'] ?: TIME) : '',
					'placeholder'	=>'YYYY-MM-DD'
				])),
				$row($L->avatar, h::input([
					'name'		=> 'user[avatar]',
					'value'		=> $user_data['avatar']
				])),
				$row($L->website, h::input([
					'name'		=> 'user[website]',
					'value'		=> $user_data['website']
				])),
				$row($L->skype, h::input([
					'name'		=> 'user[skype]',
					'value'		=> $user_data['skype']
				])),
				$row($L->about_me, h::textarea(
					$user_data['about'],
					[
						'name'		=> 'user[about]',
					]
				))
			)
		);
	break;
	case 'change_password':
		$Index->form				= true;
		$Index->buttons				= false;
		$Index->cancel_button_back	= true;
		$Page->title($L->password_changing);
		$Index->content(
			h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
				$L->password_changing
			).
			h::{'table#users_edit.cs-fullwidth-table.cs-center-all tr'}(
				h::{'th.ui-widget-header.ui-corner-all'}(
					$L->current_password.h::{'icon#current_password'}('locked')
				).
				h::{'td.ui-widget-content.ui-corner-all'}(
					h::{'input.cs-profile-current-password[type=password]'}()
				),
				h::{'th.ui-widget-header.ui-corner-all'}(
					$L->new_password.h::{'icon#new_password'}('locked')
				).
				h::{'td.ui-widget-content.ui-corner-all'}(
					h::{'input.cs-profile-new-password[type=password]'}()
				)
			).
			h::{'button.cs-profile-change-password'}(
				$L->change_password
			)
		);
	break;
}