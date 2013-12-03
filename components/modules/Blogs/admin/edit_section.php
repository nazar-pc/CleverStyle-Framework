<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\Blogs;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
$Config						= Config::instance();
$section					= Blogs::instance()->get_section($Config->route[1]);
$Index						= Index::instance();
$L							= Language::instance();
Page::instance()->title($L->editing_of_posts_section($section['title']));
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/Blogs/browse_sections';
$Index->content(
	h::{'p.lead.cs-center'}(
		$L->editing_of_posts_section($section['title'])
	).
	h::{'table.cs-table-borderless.cs-center-all tr'}(
		h::{'thead tr th'}(
			$L->parent_section,
			$L->section_title,
			($Config->core['simple_admin_mode'] ? false : h::info('section_path'))
		),
		h::{'tbody tr td'}(
			h::{'select[name=parent][size=5]'}(
				get_sections_select_section($section['id']),
				[
					'selected'	=> $section['parent']
				]
			),
			h::{'input[name=title]'}([
				'value'	=> $section['title']
			]),
			($Config->core['simple_admin_mode'] ? false : h::{'input[name=path]'}([
				'value'	=> $section['path']
			]))
		)
	).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $section['id']
	]).
	h::{'input[type=hidden][name=mode][value=edit_section]'}()
);