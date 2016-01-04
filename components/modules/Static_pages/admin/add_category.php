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
	cs\Page,
	cs\Route;

$L     = Language::instance();
$Route = Route::instance();
Page::instance()
	->title($L->addition_of_page_category)
	->content(
		h::{'form[is=cs-form][action=admin/Static_pages]'}(
			h::h2($L->addition_of_page_category).
			h::label($L->parent_category).
			h::{'select[is=cs-select][name=parent][size=5]'}(
				get_categories_list(),
				[
					'selected' => isset($Route->route[1]) ? (int)$Route->route[1] : 0
				]
			).
			h::label($L->category_title).
			h::{'input[is=cs-input-text][name=title]'}().
			h::{'label info'}('category_path').
			h::{'input[is=cs-input-text][name=path]'}().
			h::p(
				h::{'button[is=cs-button][type=submit][name=mode][value=add_category]'}(
					$L->save,
					[
						'tooltip' => $L->save_info
					]
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
