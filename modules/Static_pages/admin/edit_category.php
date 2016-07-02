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
	cs\Language\Prefix,
	cs\Page,
	cs\Request;

$L    = new Prefix('static_pages_');
$id   = (int)Request::instance()->route[1];
$data = Categories::instance()->get($id);
Page::instance()
	->title($L->editing_of_page_category($data['title']))
	->content(
		h::{'form[is=cs-form][action=admin/Static_pages]'}(
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
			h::{'label info'}('static_pages_category_path').
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
			h::p(
				h::{'button[is=cs-button][type=submit][name=mode][value=edit_category]'}(
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
