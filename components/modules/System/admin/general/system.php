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
			cs\Index,
			cs\Language;
$Config	= Config::instance();
$L		= Language::instance();
$sa		= $Config->core['simple_admin_mode'];
Index::instance()->content(
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr| td'}(
		core_input('site_mode', 'radio'),
		core_input('closed_title'),
		core_textarea('closed_text', 'SIMPLE_EDITOR'),
		core_input('title_delimiter'),
		core_input('title_reverse', 'radio'),
		core_textarea('footer_text', 'SIMPLE_EDITOR'),
		core_input('show_footer_info', 'radio'),
		core_input('show_tooltips', 'radio', false),
		core_input('og_support', 'radio'),
		core_input('simple_admin_mode', 'radio'),
		!$sa ? [
			$L->debug,
			[
				h::{'table tr| td'}(
					core_input('show_db_queries', 'radio'),
					core_input('show_cookies', 'radio')
				),
				[
					'class'	=> 'cs-padding-left'
				]
			]
		] : false,
		!$sa ? [
			h::info('routing'),
			h::{'table.cs-table-borderless tr| td'}(
				[
					h::info('routing_in'),
					h::info('routing_out')
				],
				[
					h::textarea(
						$Config->routing['in'],
						[
							'name'				=> 'routing[in]'
						]
					),
					h::textarea(
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
			h::{'table.cs-table-borderless tr| td'}(
				[
					h::info('replace_in'),
					h::info('replace_out')
				],
				[
					h::textarea(
						$Config->replace['in'],
						[
							'name'			=> 'replace[in]'
						]
					),
					h::textarea(
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
