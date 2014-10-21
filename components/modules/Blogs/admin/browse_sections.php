<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\Blogs;
use			h,
			cs\Index,
			cs\Language,
			cs\Page;
$Index			= Index::instance();
$L				= Language::instance();
$Index->buttons	= false;
Page::instance()->title($L->browse_sections);
$Index->content(
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			[
				$L->blogs_sections,
				[
					'style'	=> 'width: 80%'
				]
			],
			$L->action
		).
		h::{'cs-table-row| cs-table-cell'}(
			get_sections_rows()
		)
	).
	h::{'p.cs-left a.uk-button'}(
		$L->add_section,
		[
			'href'	=> 'admin/Blogs/add_section'
		]
	)
);
