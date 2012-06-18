<?php
global $L, $Config, $Index, $Cache;
$a = &$Index;
$a->content(
	h::{'table.cs-admin-table.cs-left-even.cs-right-odd'}(
		h::tr(
			h::{'td info'}('gzip_compression').
			h::{'td input[type=radio]'}([
				'name'			=> 'core[gzip_compression]',
				'checked'		=> $Config->core['gzip_compression'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on],
				'add'			=> zlib_compression() ? ' disabled' : ''
			])
		).
		h::tr(
			h::{'td info'}('cache_compress_js_css').
			h::{'td input[type=radio]'}([
				'name'			=> 'core[cache_compress_js_css]',
				'checked'		=> $Config->core['cache_compress_js_css'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		).
		h::tr(
			h::{'td info'}('inserts_limit').
			h::{'td input.cs-form-element[type=number]'}([
				'name'			=> 'core[inserts_limit]',
				'value'			=> $Config->core['inserts_limit'],
				'min'			=> 1
			])
		).
		h::tr(
			h::{'td info'}('update_ratio').
			h::td(
				h::{'input.cs-form-element[type=number]'}([
					'name'			=> 'core[update_ratio]',
					'value'			=> $Config->core['update_ratio'],
					'min'			=> 0,
					'max'			=> 100
				]).
				'%'
			)
		).
		h::tr(
			h::{'td div#clean_cache'}().
			h::{'td div#clean_pcache'}()
		).
		h::tr(
			h::{'td button'}(
				$L->clean_settings_cache,
				$Cache->cache_state() ? [
					'onMouseDown'	=> 'admin_cache('.
						'\'#clean_cache\','.
						'\''.$Config->server['base_url'].'/api/'.MODULE.'/admin/cache/clean_cache\''.
					');'
				] : ['disabled']
			).
			h::{'td button'}(
				$L->clean_scripts_styles_cache,
				[
					'onMouseDown'	=> $Config->core['cache_compress_js_css'] ? 'admin_cache('.
						'\'#clean_pcache\','.
						'\''.$Config->server['base_url'].'/api/'.MODULE.'/admin/cache/clean_pcache\''.
					');' : '',
					$Config->core['cache_compress_js_css'] ? '' : 'disabled'
				]
			)
		)
	)
);