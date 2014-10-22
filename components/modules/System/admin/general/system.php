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
			cs\Index;
$Config	= Config::instance();
$sa		= $Config->core['simple_admin_mode'];
Index::instance()->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		core_input('site_mode', 'radio'),
		core_input('closed_title'),
		core_textarea('closed_text', 'SIMPLE_EDITOR'),
		core_input('title_delimiter'),
		core_input('title_reverse', 'radio'),
		core_input('show_tooltips', 'radio', false),
		core_input('simple_admin_mode', 'radio'),
		!$sa ? [
			h::info('routing'),
			h::{'cs-table[center] cs-table-row| cs-table-cell'}(
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
			h::{'cs-table[center] cs-table-row| cs-table-cell'}(
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
