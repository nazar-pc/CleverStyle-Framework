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
global $L, $Config, $Index;
$sa	= $Config->core['simple_admin_mode'];
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		core_input('site_mode', 'radio'),
		core_input('closed_title'),
		core_textarea('closed_text', 'EDITORH'),
		core_input('title_delimiter'),
		core_input('title_reverse', 'radio'),
		core_textarea('footer_text', 'EDITORH'),
		core_input('show_footer_info', 'radio'),
		core_input('show_tooltips', 'radio'),
		core_input('og_support', 'radio'),
		core_input('simple_admin_mode', 'radio'),
		!$sa ? core_input('cache_sync', 'radio') : false,
		!$sa ? core_input('cookie_sync', 'radio') : false,
		!$sa ? [
			h::info('debug'),
			h::{'input[type=radio]'}([
				'name'			=> 'core[debug]',
				'checked'		=> $Config->core['debug'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on],
				'OnClick'		=> ['$(\'#debug_form\').hide();', '$(\'#debug_form\').show();']
			])
		] : false,
		!$sa ? [
			'',
			[
				h::{'table tr| td'}(
					core_input('show_objects_data', 'radio'),
					core_input('show_db_queries', 'radio'),
					core_input('show_cookies', 'radio')
				),
				[
					'style' => ($Config->core['debug'] == 0 ? 'display: none;' : ''),
					'id'	=> 'debug_form',
					'class'	=> 'cs-padding-left'
				]
			]
		] : false,
		core_input('on_error_globals_dump', 'radio'),
		!$sa ? [
			h::info('routing'),
			h::{'table#.cs-fullwidth-table tr| td'}(
				[
					h::info('routing_in'),
					h::info('routing_out')
				],
				[
					h::{'textarea.cs-wide-textarea'}(
						$Config->routing['in'],
						[
							'name'				=> 'routing[in]'
						]
					),
					h::{'textarea.cs-wide-textarea'}(
						$Config->routing['out'],
						[
							'name'				=> 'routing[out]'
						]
					)
				]
			)
		] : false,
		!$sa ? [
			h::info('replace'),
			h::{'table#.cs-fullwidth-table tr| td'}(
				[
					h::info('replace_in'),
					h::info('replace_out')
				],
				[
					h::{'textarea.cs-wide-textarea'}(
						$Config->replace['in'],
						[
							'name'			=> 'replace[in]'
						]
					),
					h::{'textarea.cs-wide-textarea'}(
						$Config->replace['out'],
						[
							'name'			=> 'replace[out]'
						]
					)
				]
			)
		] : false
	)
);