<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
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
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			[
				$L->page_title,
				[
					'style'	=> 'width: 80%'
				]
			],
			$L->action
		).
		h::{'cs-table-row| cs-table-cell'}(
			get_pages_rows()
		)
	).
	h::{'p.cs-left a.uk-button'}(
		[
			$L->add_page,
			[
				'href'	=> 'admin/Static_pages/add_page/'.array_slice($rc, -1)[0]
			]
		]
	)
);
