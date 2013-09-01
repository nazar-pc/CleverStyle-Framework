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
			cs\Index,
			cs\Language;
$Index			= Index::instance();
$L				= Language::instance();
$Index->buttons	= false;
$Index->content(
	h::{'table.cs-left-all.cs-table'}(
		h::{'thead tr th'}(
			[
				$L->pages_category,
				[
					'style'	=> 'width: 80%'
				]
			],
			$L->action
		).
		h::{'tbody tr| td'}(
			get_categories_rows()
		)
	).
	h::{'p.cs-left'}($L->index_page_path).
	h::{'p.cs-left a.cs-button'}(
		[
			$L->add_category,
			[
				'href'	=> 'admin/OAuth2/add_category'
			]
		],
		[
			$L->add_page,
			[
				'href'	=> 'admin/OAuth2/add_page'
			]
		]/*,
		[
			$L->add_page_live,
			[
				'href'	=> 'admin/OAuth2/add_page_live'
			]
		]*/
	)
);