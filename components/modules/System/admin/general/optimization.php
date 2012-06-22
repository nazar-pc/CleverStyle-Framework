<?php
global $L, $Config, $Index, $Cache;
$a = &$Index;
$a->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr'}([
		h::td([
			h::info('gzip_compression'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[gzip_compression]',
				'checked'		=> $Config->core['gzip_compression'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on],
				'add'			=> zlib_compression() ? ' disabled' : ''
			])
		]),

		h::td([
			h::info('cache_compress_js_css'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[cache_compress_js_css]',
				'checked'		=> $Config->core['cache_compress_js_css'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		]),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('inserts_limit'),
			h::{'input.cs-form-element[type=number]'}([
				'name'			=> 'core[inserts_limit]',
				'value'			=> $Config->core['inserts_limit'],
				'min'			=> 1
			])
		]) : false),

		(!$Config->core['simple_admin_mode'] ? h::td([
			h::info('update_ratio'),
			h::{'input.cs-form-element[type=number]'}([
				'name'			=> 'core[update_ratio]',
				'value'			=> $Config->core['update_ratio'],
				'min'			=> 0,
				'max'			=> 100
			]).
			'%'
		]) : false),

		h::td([
			h::{'div#clean_cache'}(),
			h::{'div#clean_pcache'}()
		]),

		h::td([
			h::button(
				$L->clean_settings_cache,
				$Cache->cache_state() ? [
					'onMouseDown'	=> 'admin_cache('.
						'\'#clean_cache\','.
						'\''.$Config->server['base_url'].'/api/'.MODULE.'/admin/cache/clean_cache\''.
					');'
				] : ['disabled']
			),
			h::button(
				$L->clean_scripts_styles_cache,
				[
					'onMouseDown'	=> $Config->core['cache_compress_js_css'] ? 'admin_cache('.
						'\'#clean_pcache\','.
						'\''.$Config->server['base_url'].'/api/'.MODULE.'/admin/cache/clean_pcache\''.
					');' : '',
					$Config->core['cache_compress_js_css'] ? '' : 'disabled'
				]
			)
		])
	])
);