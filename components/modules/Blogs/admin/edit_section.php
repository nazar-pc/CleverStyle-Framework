<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route;
$Config  = Config::instance();
$section = Sections::instance()->get(Route::instance()->route[1]);
$Index   = Index::instance();
$L       = Language::instance();
Page::instance()->title($L->editing_of_posts_section($section['title']));
$Index->cancel_button_back = true;
$Index->action             = 'admin/Blogs/browse_sections';
$Index->content(
	h::{'h2.cs-text-center'}(
		$L->editing_of_posts_section($section['title'])
	).
	h::label($L->parent_section).
	h::{'select[is=cs-select][name=parent][size=5]'}(
		get_sections_select_section($section['id']),
		[
			'selected' => $section['parent']
		]
	).
	h::label($L->section_title).
	h::{'input[is=cs-input-text][name=title]'}(
		[
			'value' => $section['title']
		]
	).
	($Config->core['simple_admin_mode'] ? false :
		h::{'label info'}('section_path').
		h::{'input[is=cs-input-text][name=path]'}(
			[
				'value' => $section['path']
			]
		)
	).
	h::{'input[type=hidden][name=id]'}(
		[
			'value' => $section['id']
		]
	).
	h::{'input[type=hidden][name=mode][value=edit_section]'}().
	h::br()
);
