<?php
/**
 * @package		ClevereStyle CMS
 * @subpackage	CleverStyle theme
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\themes\CleverStyle;
use
	cs\Config,
	cs\DB,
	cs\Event,
	cs\Language,
	cs\Page,
	cs\User,
	h;
/**
 * Returns array with `a` items
 *
 * @return string[]
 */
function get_main_menu () {
	$Config				= Config::instance();
	$L					= Language::instance();
	$User				= User::instance();
	$main_menu_items	= [];
	/**
	 * Administration item if allowed
	 */
	if ($User->admin() || ($Config->can_be_admin() && $Config->core['ip_admin_list_only'])) {
		$main_menu_items[] = h::a(
			$L->administration,
			[
				'href' => 'admin'
			]
		);
	}
	/**
	 * Home item
	 */
	$main_menu_items[] = h::a(
		$L->home,
		[
			'href' => '/'
		]
	);
	/**
	 * All other active modules if permissions allow to visit
	 */
	foreach ($Config->components['modules'] as $module => $module_data) {
		if (
			$module != 'System' &&
			$module_data['active'] == 1 &&
			$module != $Config->core['default_module'] &&
			!@file_get_json(MODULES."/$module/meta.json")['hide_in_menu'] &&
			$User->get_permission($module, 'index') &&
			(
				file_exists(MODULES."/$module/index.php") ||
				file_exists(MODULES."/$module/index.html") ||
				file_exists(MODULES."/$module/index.json")
			)
		) {
			$main_menu_items[] = h::a(
				$L->$module,
				[
					'href' => path($L->$module)
				]
			);
		}
	}
	return $main_menu_items;
}
/**
 * Getting header information about user, sign in/sign up forms, etc.
 *
 * @return string
 */
function get_header_info () {
	$L		= Language::instance();
	$User	= User::instance(true);
	if ($User->user()) {
		$content	= h::{'div.cs-header-user-block.active'}(
			h::b(
				"$L->hello, ".$User->username().'! '.
				h::{'icon.cs-header-sign-out-process'}(
					'sign-out',
					[
						'style'			=> 'cursor: pointer;',
						'data-title'	=> $L->sign_out
					]
				)
			).
			h::div(
				h::a(
					$L->profile,
					[
						'href'	=> path($L->profile)."/$User->login"
					]
				).
				' | '.
				h::a(
					$L->settings,
					[
						'href'	=> path($L->profile).'/'.path($L->settings)
					]
				)
			)
		);
	} else {
		$external_systems_list		= '';
		Event::instance()->fire(
			'System/Page/external_sign_in_list',
			[
				'list'	=> &$external_systems_list
			]
		);
		$content	=
			h::{'div.cs-header-guest-form.active'}(
				h::b("$L->hello, $L->guest!").
				h::div(
					h::{'button.uk-button.cs-button-compact.cs-header-sign-in-slide'}(
						h::icon('sign-in').
						$L->sign_in
					).
					h::{'button.uk-button.cs-button-compact.cs-header-registration-slide'}(
						h::icon('pencil').
						$L->sign_up,
						[
							'data-title'	=> $L->quick_registration_form
						]
					)
				)
			).
			h::{'div.cs-header-restore-password-form'}(
				h::{'input.cs-header-restore-password-email[tabindex=1]'}([
					'placeholder'		=> $L->login_or_email,
					'autocapitalize'	=> 'off',
					'autocorrect'		=> 'off'
				]).
				h::br().
				h::{'button.uk-button.cs-button-compact.cs-header-restore-password-process[tabindex=2]'}(
					h::icon('question').
					$L->restore_password
				).
				h::{'button.uk-button.cs-button-compact.cs-header-back'}(
					h::icon('chevron-down'),
					[
						'data-title'	=> $L->back
					]
				)
			).
			h::{'div.cs-header-registration-form'}(
				h::{'input.cs-header-registration-email[type=email]'}([
					'placeholder'		=> $L->email,
					'autocapitalize'	=> 'off',
					'autocorrect'		=> 'off'
				]).
				h::br().
				h::{'button.uk-button.cs-button-compact.cs-header-registration-process'}(
					h::icon('pencil').
					$L->sign_up
				).
				h::{'button.uk-button.cs-button-compact.cs-header-back'}(
					h::icon('chevron-down'),
					[
						'data-title'	=> $L->back
					]
				)
			).
			h::{'form.cs-header-sign-in-form'}(
				h::{'input.cs-header-sign-in-email'}([
					'placeholder'		=> $L->login_or_email,
					'autocapitalize'	=> 'off',
					'autocorrect'		=> 'off'
				]).
				h::{'input.cs-header-user-password[type=password]'}([
					'placeholder'	=> $L->password
				]).
				h::br().
				h::{'button.uk-button.cs-button-compact[type=submit]'}(
					h::icon('sign-in').
					$L->sign_in
				).
				h::{'button.uk-button.cs-button-compact.cs-header-back'}(
					h::icon('chevron-down'),
					[
						'data-title'	=> $L->back
					]
				).
				h::{'button.uk-button.cs-button-compact.cs-header-restore-password-slide'}(
					h::icon('question'),
					[
						'data-title'	=> $L->restore_password
					]
				)
			).
			$external_systems_list;
	}
	return $content;
}
/**
 * Getting footer information
 *
 * @return string
 */
function get_footer () {
	$db = class_exists('cs\\DB', false) ? DB::instance() : null;
	/**
	 * Some useful details about page execution process, will be called directly before output
	 */
	Event::instance()->on(
		'System/Page/display',
		function () {
			$Page		= Page::instance();
			$Page->Html	= str_replace(
				[
					'<!--generate time-->',
					'<!--peak memory usage-->'
				],
				[
					format_time(round(microtime(true) - MICROTIME, 5)),
					format_filesize(memory_get_usage(), 5).h::{'sup[level=0]'}(format_filesize(memory_get_peak_usage(), 5))
				],
				$Page->Html
			);
		}
	);
	return h::div(
		Language::instance()->page_footer_info(
			'<!--generate time-->',
			$db ? $db->queries() : 0,
			format_time(round($db ? $db->time() : 0, 5)),
			'<!--peak memory usage-->'
		),
		'© Powered by <a target="_blank" href="http://cleverstyle.org/cms" title="CleverStyle CMS">CleverStyle CMS</a>'
	);
}
