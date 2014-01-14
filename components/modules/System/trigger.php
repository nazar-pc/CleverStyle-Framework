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
Trigger::instance()->register(
	'System/User/construct/after',
	function () {
		$Config				= Config::instance();
		if (!$Config->core['multilingual']) {
			return;
		}
		$relative_address	= $Config->server['relative_address'];
		$Cache				= Cache::instance();
		if (
			!FIXED_LANGUAGE &&
			$_SERVER['REQUEST_METHOD'] == 'GET' &&
			$Cache->cache_state() &&
			Core::instance()->cache_engine != 'BlackHole'
		) {
			$clang	= Language::instance()->clang;
			if (!HOME) {
				header("Location: /$clang/$relative_address", true, 301);
			} else {
				header("Location: /$clang", true, 301);
			}
		}
		$base_url				= substr($Config->base_url(), 0, -3);
		Page::instance()->Head	.=
			h::{'link[rel=alternate]'}([
				'hreflang'	=> 'x-default',
				'href'		=> !HOME ? "$base_url/$relative_address" : "$base_url"
			]).
			h::{'link[rel=alternate]|'}(array_map(
				function ($lang) use ($base_url, $relative_address) {
					return [
						'hreflang'	=> $lang,
						'href'		=> "$base_url/$lang/$relative_address"
					];
				},
				array_values($Cache->get('languages/clangs', function () use ($Config) {
					return $Config->update_clangs();
				})) ?: []
			));
	}
);
