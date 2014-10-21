<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System;
use			h,
			cs\Config,
			cs\Cache,
			cs\Index,
			cs\Language;
$Config	= Config::instance();
$L		= Language::instance();
$sa		= $Config->core['simple_admin_mode'];
Index::instance()->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		core_input('gzip_compression', 'radio', null, zlib_compression()),
		core_input('cache_compress_js_css', 'radio'),
		core_input('vulcanization', 'radio'),
		core_input('put_js_after_body', 'radio'),
		(!$sa ? core_input('inserts_limit', 'number', null, false, 1) : false),
		(!$sa ? core_input('update_ratio', 'number', null, false, 0, 100) : false),
		[
			h::{'div#clean_cache'}(),
			h::{'div#clean_pcache'}()
		],
		[
			h::{'input[style=width:auto;]'}([
				'placeholder'	=> $L->partial_cache_cleaning,
				'style'			=> $Config->core['simple_admin_mode'] ? 'display:none;' : false
			]).
			h::{'button.uk-button'}(
				$L->clean_settings_cache,
				Cache::instance()->cache_state() ? [
					'onMouseDown'	=> "cs.admin_cache('#clean_cache', '{$Config->base_url()}/api/System/admin/cache/clean_cache', $(this).prev().val());"
				] : ['disabled']
			),
			h::{'button.uk-button'}(
				$L->clean_scripts_styles_cache,
				$Config->core['cache_compress_js_css'] ? [
					'onMouseDown'	=> "cs.admin_cache('#clean_pcache', '{$Config->base_url()}/api/System/admin/cache/clean_pcache');"
				] : ['disabled']
			)
		]
	)
);
