<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	h;

/**
 * Multilingual functionality: redirects and necessary meta-tags
 */
Event::instance()
	->on(
		'System/Session/init/after',
		function () {
			$Config  = Config::instance();
			$Session = Session::instance();
			$user_id = $Session->get_user();
			/**
			 * If not guest - apply some individual settings
			 */
			if ($user_id != User::GUEST_ID) {
				$User     = User::instance();
				$timezone = $User->get('timezone', $user_id);
				if ($timezone && date_default_timezone_get() != $timezone) {
					date_default_timezone_set($timezone);
				}
				$L = Language::instance();
				/**
				 * Change language if configuration is multilingual and this is not page with localized url
				 */
				$language = $User->get('language', $user_id);
				if ($Config->core['multilingual'] && !$L->url_language() && $language) {
					$L->change($language);
				}
			}
			$Request = Request::instance();
			/**
			 * Security check
			 */
			if (
				$Request->header('x-requested-with') !== 'XMLHttpRequest' &&
				!$Request->data('session') != $Session->get_id() &&
				$Request->header('origin') != $Config->core_url()
			) {
				$Request->data = [];
			}
		}
	)
	->on(
		'System/User/construct/after',
		function () {
			$Config = Config::instance();
			if (!$Config->core['multilingual']) {
				return;
			}
			$Request          = Request::instance();
			$relative_address = $Request->path_normalized;
			$Page             = Page::instance();
			$core_url         = $Config->core_url();
			$base_url         = $Config->base_url();
			$Page->Head .= h::link(
				[
					'hreflang' => 'x-default',
					'href'     => $Request->home_page ? $core_url : "$core_url/$relative_address",
					'rel'      => 'alternate'
				]
			);
			$clangs = Cache::instance()->get(
				'languages/clangs',
				function () use ($Config) {
					$clangs = [];
					foreach ($Config->core['active_languages'] as $language) {
						$clangs[] = file_get_json_nocomments(LANGUAGES."/$language.json")['clang'];
					}
					return $clangs;
				}
			);
			foreach ($clangs as $clang) {
				$Page->Head .= h::link(
					[
						'hreflang' => $clang,
						'href'     => "$base_url/$clang/$relative_address",
						'rel'      => 'alternate'
					]
				);
			}
		}
	)
	->on(
		'admin/System/Menu',
		function () {
			$Config    = Config::instance();
			$L         = Language::prefix('system_admin_');
			$Menu      = Menu::instance();
			$Request   = Request::instance();
			$structure = $Config->core['simple_admin_mode'] ? file_get_json(__DIR__.'/admin/index_simple.json') : file_get_json(__DIR__.'/admin/index.json');
			foreach ($structure as $section => $items) {
				$Menu->add_section_item(
					'System',
					$L->$section,
					[
						'href'    => "admin/System/$section",
						'primary' => $Request->route_path(0) == $section
					]
				);
				foreach ($items as $item) {
					$Menu->add_item(
						'System',
						$L->$item,
						[
							'href'    => "admin/System/$section/$item",
							'primary' => $Request->route_path(0) == $section && $Request->route_path(1) == $item
						]
					);
				}
			}
		}
	);
