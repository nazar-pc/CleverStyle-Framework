<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
/**
 * Multilingual functionality: redirects and necessary meta-tags
 */
Trigger::instance()
	->register(
		'System/Config/routing_replace',
		function ($data) {
			$rc		= explode('/', $data['rc']);
			$Config	= Config::instance();
			if (!isset($rc[0])) {
				return;
			}
			$L  = Language::instance();
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
						$rc[0]	= 'profile';
				}
			}
			if (isset($rc[1])) {
				switch ($rc[1]) {
					case path($L->settings):
						$rc[1] = 'settings';
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
	->register(
		'System/User/construct/after',
		function () {
			$Config				= Config::instance();
			if (!$Config->core['multilingual']) {
				return;
			}
			$relative_address	= $Config->server['relative_address'];
			$Core				= Core::instance();
			$Cache				= Cache::instance();
			if (
				!$Core->fixed_language &&
				$_SERVER['REQUEST_METHOD'] == 'GET' &&
				$Cache->cache_state() &&
				$Core->cache_engine != 'BlackHole'
			) {
				$clang	= Language::instance()->clang;
				if (!home_page()) {
					header("Location: /$clang/$relative_address", true, 301);
				} else {
					header("Location: /$clang", true, 301);
				}
			}
			$base_url				= substr($Config->base_url(), 0, -3);
			Page::instance()->Head	.=
				h::{'link[rel=alternate]'}([
					'hreflang'	=> 'x-default',
					'href'		=> home_page() ? $base_url : "$base_url/$relative_address"
				]).
				h::{'link[rel=alternate]|'}(array_map(
					function ($lang) use ($base_url, $relative_address) {
						return [
							'hreflang'	=> $lang,
							'href'		=> "$base_url/$lang/$relative_address"
						];
					},
					array_values(
						file_exists(CACHE.'/languages_clangs') ? file_get_json(CACHE.'/languages_clangs') : $Config->update_clangs()
					)
				));
		}
	)
	->register(
		'System/Index/construct',
		function () {
			if (admin_path()) {
				require __DIR__.'/trigger/admin.php';
			}
		}
	);
