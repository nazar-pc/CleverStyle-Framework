<?php
/**
 * @package        Static Pages
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
use
	h,
	cs\Language,
	cs\Page,
	cs\Route;

$L     = Language::instance();
$id    = (int)Route::instance()->route[1];
$title = Categories::instance()->get($id)['title'];
Page::instance()
	->title($L->deletion_of_page_category($title))
	->content(
		h::{'form[is=cs-form][action=admin/Static_pages]'}(
			h::{'h2.cs-text-center'}(
				$L->sure_to_delete_page_category($title)
			).
			h::{'input[type=hidden][name=id]'}(
				[
					'value' => $id
				]
			).
			h::p(
				h::{'button[is=cs-button][type=submit][name=mode][value=delete_category]'}(
					$L->yes
				).
				h::{'button[is=cs-button][type=button]'}(
					$L->cancel,
					[
						'onclick' => 'history.go(-1);'
					]
				)
			)
		)
	);
