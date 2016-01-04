<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
use
	h,
	cs\Language,
	cs\Page;

$L = Language::instance();
Page::instance()->content(
	h::{'table.cs-table[list]'}(
		h::{'tr th'}(
			[
				$L->pages_category,
				[
					'style' => 'width: 80%'
				]
			],
			$L->action
		).
		h::{'tr| td'}(
			get_categories_rows()
		)
	).
	h::{'p.cs-text-left'}($L->index_page_path).
	h::{'p.cs-text-left a[is=cs-link-button]'}(
		[
			$L->add_category,
			[
				'href' => 'admin/Static_pages/add_category'
			]
		],
		[
			$L->add_page,
			[
				'href' => 'admin/Static_pages/add_page'
			]
		]
	)
);
