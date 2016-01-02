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

$Config = Config::instance();
$L      = Language::instance();
$Page   = Page::instance();
$Route  = Route::instance();
$Page->title($L->addition_of_posts_section);
$Page->content(
	h::{'form[is=cs-form][action=admin/Blogs/browse_sections]'}(
		h::{'h2.cs-text-center'}(
			$L->addition_of_posts_section
		).
		h::label($L->parent_section).
		h::{'select[is=cs-select][name=parent][size=5]'}(
			get_sections_select_section(),
			[
				'selected' => isset($Route->route[1]) ? (int)$Route->route[1] : 0
			]
		).
		h::label($L->section_title).
		h::{'input[is=cs-input-text][name=title]'}().
		($Config->core['simple_admin_mode'] ? false :
			h::{'label info'}('section_path').
			h::{'input[is=cs-input-text][name=path]'}()
		).
		h::p(
			h::{'button[is=cs-button][type=submit][name=mode][value=add_section]'}(
				$L->save,
				[
					'tooltip' => $L->save_info
				]
			).
			h::{'button[is=cs-button]'}(
				$L->cancel,
				[
					'type'    => 'button',
					'onclick' => 'history.go(-1);'
				]
			)
		)
	)
);
