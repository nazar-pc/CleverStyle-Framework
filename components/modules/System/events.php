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
				$Config = Config::instance();
				$L      = Language::instance();
				/**
				 * Change language if configuration is multilingual and this is not page with localized url
				 */
				if ($Config->core['multilingual'] && !$L->url_language()) {
					$L->change($User->get('language', $user_id));
				}
			}
			$Request = Request::instance();
			/**
			 * Security check
			 */
			if (
				$Request->header('x-requested-with') !== 'XMLHttpRequest' &&
				(
					!isset($_POST['session']) || $_POST['session'] != $Session->get_id()
				)
			) {
				foreach (array_keys((array)$_POST) as $key) {
					unset($_POST[$key], $_REQUEST[$key]);
				}
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
		'System/Index/construct',
		function () {
			if (Request::instance()->current_module == 'System') {
				require __DIR__.'/events/admin.php';
			}
		}
	);
