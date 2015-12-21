<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
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
$data  = Pages::instance()->get($id);
Page::instance()->title($L->editing_of_page($data['title']));
$Index->cancel_button_back = true;
$Index->action             = 'admin/Static_pages';
$textarea                  = h::{'textarea[is=cs-textarea][autosize][name=content]'}($data['content']);
$Index->content(
	h::h2(
		$L->editing_of_page($data['title'])
	).
	h::{'table.cs-table[center] tr'}(
		h::th(
			$L->category,
			$L->page_title,
			h::info('page_path'),
			h::info('page_interface')
		),
		h::td(
			h::{'select[is=cs-select][full-width][name=category][size=5]'}(
				get_categories_list(),
				[
					'selected' => $data['category']
				]
			),
			h::{'input[is=cs-input-text][full-width][name=title]'}(
				[
					'value' => $data['title']
				]
			),
			h::{'input[is=cs-input-text][full-width][name=path]'}(
				[
					'value' => $data['path']
				]
			),
			h::{'div radio[name=interface]'}(
				[
					'checked' => $data['interface'],
					'value'   => [0, 1],
					'in'      => [$L->off, $L->on]
				]
			)
		)
	).
	h::{'table.cs-table[center] tr'}(
		h::th($L->content),
		h::td(
			$data['interface'] ? h::cs_editor($textarea) : $textarea
		)
	).
	h::{'input[type=hidden][name=id]'}(
		[
			'value' => $id
		]
	).
	h::{'input[type=hidden][name=mode][value=edit_page]'}()
);
