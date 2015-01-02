<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\Blogs;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
$Config						= Config::instance();
$Index						= Index::instance();
$L							= Language::instance();
Page::instance()->title($L->addition_of_posts_section);
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/Blogs/browse_sections';
$Index->content(
	h::{'h2.cs-center'}(
		$L->addition_of_posts_section
	).
	h::{'cs-table[center][with-header] cs-table-row| cs-table-cell'}(
		[
			$L->parent_section,
			$L->section_title,
			($Config->core['simple_admin_mode'] ? false : h::info('section_path'))
		],
		[
			h::{'select[name=parent][size=5]'}(
				get_sections_select_section(),
				[
					'selected'	=> isset($Config->route[1]) ? (int)$Config->route[1] : 0
				]
			),
			h::{'input[name=title]'}(),
			($Config->core['simple_admin_mode'] ? false : h::{'input[name=path]'}())
		]
	).
	h::{'input[type=hidden][name=mode][value=add_section]'}()
);
