<?php
/**
 * @package		ClevereStyle CMS
 * @subpackage	CleverStyle theme
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\themes\CleverStyle;
use
	cs\Config,
	cs\DB,
	cs\Language,
	cs\Page,
	cs\Trigger,
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
	if ($User->admin() || ($Config->can_be_admin && $Config->core['ip_admin_list_only'])) {
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
			$module_data['active'] == 1 &&
			$module != $Config->core['default_module'] &&
			$module != 'System' &&
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
		$content	= h::{'div.cs-header-user-block'}(
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
		Trigger::instance()->run(
			'System/Page/external_sign_in_list',
			[
				'list'	=> &$external_systems_list
			]
		);
		$content	=
			h::{'div.cs-header-guest-form'}(
				h::b("$L->hello, $L->guest!").
				h::div(
					h::{'button.cs-header-sign-in-slide.cs-button-compact.uk-icon-sign-in'}($L->sign_in).
					h::{'button.cs-header-registration-slide.cs-button-compact.uk-icon-pencil'}(
						$L->sign_up,
						[
							'data-title'	=> $L->quick_registration_form
						]
					)
				)
			).
			h::{'div.cs-header-restore-password-form'}(
				h::{'input.cs-no-ui.cs-header-restore-password-email[tabindex=1]'}([
					'placeholder'		=> $L->login_or_email,
					'autocapitalize'	=> 'off',
					'autocorrect'		=> 'off'
				]).
				h::br().
				h::{'button.cs-header-restore-password-process.cs-button-compact.uk-icon-question[tabindex=2]'}($L->restore_password).
				h::{'button.cs-button-compact.cs-header-back[tabindex=3]'}(
					h::icon('chevron-down'),
					[
						'data-title'	=> $L->back
					]
				),
				[
					'style'	=> 'display: none;'
				]
			).
			h::{'div.cs-header-registration-form'}(
				h::{'input.cs-no-ui.cs-header-registration-email[type=email][tabindex=1]'}([
					'placeholder'		=> $L->email,
					'autocapitalize'	=> 'off',
					'autocorrect'		=> 'off'
				]).
				h::br().
				h::{'button.cs-header-registration-process.cs-button-compact.uk-icon-pencil[tabindex=2]'}($L->sign_up).
				h::{'button.cs-button-compact.cs-header-back[tabindex=4]'}(
					h::icon('chevron-down'),
					[
						'data-title'	=> $L->back
					]
				),
				[
					'style'	=> 'display: none;'
				]
			).
			h::{'form.cs-header-sign-in-form.cs-no-ui'}(
				h::{'input.cs-no-ui.cs-header-sign-in-email[tabindex=1]'}([
					'placeholder'		=> $L->login_or_email,
					'autocapitalize'	=> 'off',
					'autocorrect'		=> 'off'
				]).
				h::{'input.cs-no-ui.cs-header-user-password[type=password][tabindex=2]'}([
					'placeholder'	=> $L->password
				]).
				h::br().
				h::{'button.cs-button-compact.uk-icon-sign-in[tabindex=3][type=submit]'}($L->sign_in).
				h::{'button.cs-button-compact.cs-header-back[tabindex=5]'}(
					h::icon('chevron-down'),
					[
						'data-title'	=> $L->back
					]
				).
				h::{'button.cs-button-compact.cs-header-restore-password-slide[tabindex=4]'}(
					h::icon('question'),
					[
						'data-title'	=> $L->restore_password
					]
				),
				[
					'style'	=> 'display: none;'
				]
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
	Trigger::instance()->register(
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
			$db ? $db->queries : 0,
			format_time(round($db ? $db->time : 0, 5)),
			'<!--peak memory usage-->'
		),
		'© Powered by <a target="_blank" href="http://cleverstyle.org/cms" title="CleverStyle CMS">CleverStyle CMS</a>'
	);
}
