<?php
global $L, $Config, $Index, $Cache;
$a = &$Index;
$a->content(
	h::{'table.admin_table.left_even.right_odd'}(
		h::tr(
			h::{'td info'}('disk_cache').
			h::{'td input[type=radio]'}([
				'name'			=> 'core[disk_cache]',
				'checked'		=> $Config->core['disk_cache'],
				'value'			=> array(0, 1),
				'in'			=> array($L->off, $L->on)
			])
		).
		h::tr(
			h::{'td info'}('disk_cache_size').
			h::{'td input.form_element[type=number]'}([
				'name'			=> 'core[disk_cache_size]',
				'value'			=> $Config->core['disk_cache_size'],
				'min'			=> 0
			])
		).
		h::tr(
			h::{'td info'}('memcache').
			h::{'td input[type=radio]'}([
				'name'			=> 'core[memcache]',
				'checked'		=> $Config->core['memcache'],
				'value'			=> array(0, 1),
				'in'			=> array($L->off, $L->on),
				'add'			=> memcache() ? '' : ' disabled'
			])
		).
/*		h::tr(
			h::{'td info'}('memcached').
			h::{'td input[type=radio]'}([
				'name'			=> 'core[memcached]',
				'checked'		=> $Config->core['memcached'],
				'value'			=> array(0, 1),
				'in'			=> array($L->off, $L->on),
				'add'			=> memcache() ? '' : ' disabled'
			])
		).*/
		h::tr(
			h::{'td info'}('zlib_compression').
			h::{'td input[type=radio]'}([
				'name'			=> 'core[zlib_compression]',
				'checked'		=> $Config->core['zlib_compression'],
				'value'			=> array(0, 1),
				'in'			=> array($L->off, $L->on),
				'onClick'		=> zlib() ? array('$(\'#zlib_compression\').hide();', '$(\'#zlib_compression\').show();') : '',
				'add'			=> zlib_autocompression() ? ' disabled' : ''
			])
		).
		h::{'tr#zlib_compression'}(
			h::td($L->zlib_compression_level).
			h::{'td input.form_element[type=range]'}([
				'name'			=> 'core[zlib_compression_level]',
				'value'			=> $Config->core['zlib_compression_level'],
				'min'			=> 1,
				'max'			=> 9
			]),
			[
				'style'	=> ($Config->core['zlib_compression'] || zlib_autocompression() ? '' : 'display: none; ')
			]
		).
		h::tr(
			h::{'td info'}('gzip_compression').
			h::{'td input[type=radio]'}([
				'name'			=> 'core[gzip_compression]',
				'checked'		=> $Config->core['gzip_compression'],
				'value'			=> array(0, 1),
				'in'			=> array($L->off, $L->on),
				'add'			=> !zlib_autocompression() || $Config->core['zlib_compression'] ? '' : ' disabled'
			])
		).
		h::tr(
			h::{'td info'}('cache_compress_js_css').
			h::{'td input[type=radio]'}([
				'name'			=> 'core[cache_compress_js_css]',
				'checked'		=> $Config->core['cache_compress_js_css'],
				'value'			=> array(0, 1),
				'in'			=> array($L->off, $L->on)
			])
		).
		h::tr(
			h::{'td info'}('inserts_limit').
			h::{'td input.form_element[type=number]'}([
				'name'			=> 'core[inserts_limit]',
				'value'			=> $Config->core['inserts_limit'],
				'min'			=> 1
			])
		).
		h::tr(
			h::{'td info'}('update_ratio').
			h::td(
				h::{'input.form_element[type=number]'}([
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
				$Cache->cache ? [
					'onMouseDown'	=> 'admin_cache('.
						'\'#clean_cache\','.
						'\''.$Config->server['base_url'].'/api/'.MODULE.'/admin/cache/flush_cache\''.
					');'
				] : ['disabled']
			).
			h::{'td button'}(
				$L->clean_scripts_styles_cache,
				[
					'onMouseDown'	=> $Config->core['cache_compress_js_css'] ? 'admin_cache('.
						'\'#clean_pcache\','.
						'\''.$Config->server['base_url'].'/api/'.MODULE.'/admin/cache/flush_pcache\''.
					');' : '',
					$Config->core['cache_compress_js_css'] ? '' : 'disabled'
				]
			)
		)
	)
);