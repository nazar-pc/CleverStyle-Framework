<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h,
			cs\Config,
			cs\Index,
			cs\Language;
$Index			= Index::instance();
$L				= Language::instance();
$rc				= Config::instance()->route;
$Index->buttons	= false;
$Index->content(
	h::{'table.cs-left-all.cs-table'}(
		h::{'thead tr th'}(
			[
				$L->page_title,
				[
					'style'	=> 'width: 80%'
				]
			],
			$L->action
		).
		h::{'tbody tr| td'}(
			get_pages_rows()
		)
	).
	h::{'p.cs-left a.cs-button'}(
		[
			$L->add_page,
			[
				'href'	=> 'admin/Static_pages/add_page/'.array_slice($rc, -1)[0]
			]
		]/*,
			[
				$L->add_page_live,
				[
					'href'	=> 'admin/Static_pages/add_page_live/'.array_slice($rc, -1)[0]
				]
			]*/
	)
);