<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h,
			cs\Index,
			cs\Language;
$Index			= Index::instance();
$L				= Language::instance();
$Index->buttons	= false;
$Index->content(
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			[
				$L->pages_category,
				[
					'style'	=> 'width: 80%'
				]
			],
			$L->action
		).
		h::{'cs-table-row| cs-table-cell'}(
			get_categories_rows()
		)
	).
	h::{'p.cs-left'}($L->index_page_path).
	h::{'p.cs-left a.uk-button'}(
		[
			$L->add_category,
			[
				'href'	=> 'admin/Static_pages/add_category'
			]
		],
		[
			$L->add_page,
			[
				'href'	=> 'admin/Static_pages/add_page'
			]
		]
	)
);
