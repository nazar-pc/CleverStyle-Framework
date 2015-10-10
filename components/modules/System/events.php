<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2013-2015, Nazar Mokrynskyi
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
		'System/Route/routing_replace',
		function ($data) {
			if ($data['rc'] == 'api/System/profile') {
				$data['rc'] = 'api/System/profile/profile';
			}
			$rc     = explode('/', $data['rc']);
			$Config = Config::instance();
			if (!isset($rc[0])) {
				return;
			}
			$L = Language::instance();
			if (
				$rc[0] == 'profile' ||
				(
					$rc[0] == path($L->profile) &&
					!isset($Config->components['modules'][$rc[0]]) &&
					!in_array($rc[0], $Config->components['plugins'])
				)
			) {
				$rc[0] = 'profile';
				switch ($rc[0]) {
					case path($L->profile):
						$rc[0] = 'profile';
				}
			} else {
				return;
			}
			// TODO: drop at least following switch block
			if (isset($rc[1])) {
				switch ($rc[1]) {
					case path($L->settings):
						$rc[1] = 'settings';
						break;
					default:
						$rc[2] = $rc[1];
						$rc[1] = 'info';
				}
				if (isset($rc[2])) {
					switch ($rc[2]) {
						case path($L->general):
							$rc[2] = 'general';
							break;
						case path($L->change_password):
							$rc[2] = 'change_password';
							break;
					}
				}
			}
			$data['rc'] = implode('/', $rc);
		}
	)
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
			/**
			 * Security check
			 */
			if (
				(
					!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest'
				) &&
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
			$relative_address = Route::instance()->relative_address;
			$Core             = Core::instance();
			$Cache            = Cache::instance();
			$L                = Language::instance();
			$Page             = Page::instance();
			/**
			 * @var _SERVER $_SERVER
			 */
			if (
				$_SERVER->request_method == 'GET' &&
				$Core->cache_engine != 'BlackHole' &&
				@Route::instance()->route[0] != 'robots.txt' &&
				!$L->url_language() &&
				$Cache->cache_state()
			) {
				$clang        = $L->clang;
				$query_string = $_SERVER->query_string ? "?$_SERVER->query_string" : '';
				if (!home_page()) {
					_header("Location: /$clang/$relative_address$query_string", true, 301);
				} else {
					_header("Location: /$clang$query_string", true, 301);
				}
				$Page->Content = '';
				interface_off();
				throw new ExitException;
			}
			$core_url = $Config->core_url();
			$base_url = $Config->base_url();
			$Page->Head .= h::{'link[rel=alternate]'}(
				[
					'hreflang' => 'x-default',
					'href'     => home_page() ? $core_url : "$core_url/$relative_address"
				]
			);
			$clangs = $Cache->get(
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
				$Page->Head .= h::{'link[rel=alternate]|'}(
					[
						'hreflang' => $clang,
						'href'     => "$base_url/$clang/$relative_address"
					]
				);
			}
		}
	)
	->on(
		'System/Index/construct',
		function () {
			if (admin_path() && current_module() == 'System') {
				require __DIR__.'/events/admin.php';
			}
		}
	);
