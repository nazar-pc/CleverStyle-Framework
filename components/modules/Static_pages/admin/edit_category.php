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
$id    = (int)Route::instance()->route[1];
$data  = Categories::instance()->get($id);
Page::instance()->title($L->editing_of_page_category($data['title']));
$Index->cancel_button_back    = true;
$Index->action                = 'admin/Static_pages';
$Index->form_attributes['is'] = 'cs-form';
$Index->content(
	h::h2($L->editing_of_page_category($data['title'])).
	h::label($L->parent_category).
	h::{'select[is=cs-select][name=parent][size=5]'}(
		get_categories_list($id),
		[
			'selected' => $data['parent']
		]
	).
	h::label($L->category_title).
	h::{'input[is=cs-input-text][name=title]'}(
		[
			'value' => $data['title']
		]
	).
	h::{'label info'}('category_path').
	h::{'input[is=cs-input-text][name=path]'}(
		[
			'value' => $data['path']
		]
	).
	h::{'input[type=hidden][name=id]'}(
		[
			'value' => $id
		]
	).
	h::{'input[type=hidden][name=mode][value=edit_category]'}().
	h::br()
);
