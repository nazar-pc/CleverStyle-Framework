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
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route;
$Index = Index::instance();
$L     = Language::instance();
$Route = Route::instance();
Page::instance()->title($L->addition_of_page_category);
$Index->cancel_button_back    = true;
$Index->action                = 'admin/Static_pages';
$Index->form_attributes['is'] = 'cs-form';
$Index->content(
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
	h::{'input[type=hidden][name=mode][value=add_category]'}().
	h::br()
);
