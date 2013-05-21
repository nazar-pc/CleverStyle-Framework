<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System;
use			h;
global $L, $Config, $Index, $Cache;
$sa	= $Config->core['simple_admin_mode'];
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		core_input('gzip_compression', 'radio', null, zlib_compression()),
		core_input('cache_compress_js_css', 'radio'),
		(!$sa ? core_input('inserts_limit', 'number', null, false, 1) : false),
		(!$sa ? core_input('update_ratio', 'number', null, false, 0, 100) : false),
		[
			h::{'div#clean_cache'}(),
			h::{'div#clean_pcache'}()
		],
		[
			h::button(
				$L->clean_settings_cache,
				$Cache->cache_state() ? [
					'onMouseDown'	=> "admin_cache('#clean_cache', '".$Config->base_url()."/api/".MODULE."/admin/cache/clean_cache');"
				] : ['disabled']
			),
			h::button(
				$L->clean_scripts_styles_cache,
				$Config->core['cache_compress_js_css'] ? [
					'onMouseDown'	=> "admin_cache('#clean_pcache', '".$Config->base_url()."/api/".MODULE."/admin/cache/clean_pcache');"
				] : ['disabled']
			)
		]
	)
);