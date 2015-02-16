<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     System module
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
use
	h;
/**
 * Multilingual functionality: redirects and necessary meta-tags
 */
Event::instance()
	->on(
		'System/Config/routing_replace',
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
		'System/User/construct/after',
		function () {
			$Config = Config::instance();
			if (!$Config->core['multilingual']) {
				return;
			}
			$relative_address = $Config->server['relative_address'];
			$Core             = Core::instance();
			$Cache            = Cache::instance();
			$L                = Language::instance();
			/**
			 * @var _SERVER $_SERVER
			 */
			if (
				$_SERVER->request_method == 'GET' &&
				$Core->cache_engine != 'BlackHole' &&
				@$Config->route[0] != 'robots.txt' &&
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
			}
			$base_url = substr($Config->base_url(), 0, -3);
			Page::instance()->Head .=
				h::{'link[rel=alternate]'}([
					'hreflang' => 'x-default',
					'href'     => home_page() ? $base_url : "$base_url/$relative_address"
				]).
				h::{'link[rel=alternate]|'}(array_map(
					function ($lang) use ($base_url, $relative_address) {
						return [
							'hreflang' => $lang,
							'href'     => "$base_url/$lang/$relative_address"
						];
					},
					$Cache->get('language/clangs', function () use ($Config) {
						$clangs = [];
						foreach ($Config->core['active_languages'] as $language) {
							$clangs[] = file_get_json_nocomments(LANGUAGES."/$language.json")['clang'];
						}
						return $clangs;
					})
				));
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
