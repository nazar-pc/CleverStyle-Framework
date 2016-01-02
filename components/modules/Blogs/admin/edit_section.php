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
	cs\Language,
	cs\Page,
	cs\Route;

$section = Sections::instance()->get(Route::instance()->route[1]);
$Config  = Config::instance();
$L       = Language::instance();
$Page    = Page::instance();
$Page->title($L->editing_of_posts_section($section['title']));
$Page->content(
	h::{'form[is=cs-form][action=admin/Blogs/browse_sections]'}(
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
		h::p(
			h::{'button[is=cs-button][type=submit][name=mode][value=edit_section]'}(
				$L->save
			).
			h::{'button[is=cs-button]'}(
				$L->cancel,
				[
					'type'    => 'button',
					'onclick' => 'history.go(-1);'
				]
			)
		).
		h::{'input[type=hidden][name=id]'}(
			[
				'value' => $section['id']
			]
		)
	)
);
