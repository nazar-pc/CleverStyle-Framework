<?php
namespace	cs\modules\System;
use			\h;
global $L, $Config, $Index, $Cache;
$sa	= $Config->core['simple_admin_mode'];
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		system_input_core('gzip_compression', 'radio', null, zlib_compression()),
		system_input_core('cache_compress_js_css', 'radio'),
		(!$sa ? system_input_core('inserts_limit', 'number', null, false, 1) : false),
		(!$sa ? system_input_core('update_ratio', 'number', null, false, 0, 100) : false),
		[
			h::{'div#clean_cache'}(),
			h::{'div#clean_pcache'}()
		],
		[
			h::button(
				$L->clean_settings_cache,
				$Cache->cache_state() ? [
					'onMouseDown'	=> "admin_cache('#clean_cache', '".$Config->server['base_url']."/api/".MODULE."/admin/cache/clean_cache');"
				] : ['disabled']
			),
			h::button(
				$L->clean_scripts_styles_cache,
				$Config->core['cache_compress_js_css'] ? [
					'onMouseDown'	=> "admin_cache('#clean_pcache', '".$Config->server['base_url']."/api/".MODULE."/admin/cache/clean_pcache');"
				] : ['disabled']
			)
		]
	)
);