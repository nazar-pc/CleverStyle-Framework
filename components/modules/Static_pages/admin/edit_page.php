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
$Index->content(
	h::{'h2.cs-text-center'}(
		$L->editing_of_page($data['title'])
	).
	h::{'table.cs-table[center] tr'}(
		h::th(
			$L->category,
			$L->page_title,
			h::info('page_path'),
			h::info('page_interface')
		),
		h::tr(
			h::{'select[is=cs-select][name=category][size=5]'}(
				get_categories_list(),
				[
					'selected' => $data['category']
				]
			),
			h::{'input[name=title]'}(
				[
					'value' => $data['title']
				]
			),
			h::{'input[name=path]'}(
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
	h::{'table.cs-table[center] tr| td'}(
		[
			$L->content,
			h::{'textarea[is=cs-textarea][autosize][name=content]'}(
				$data['content'],
				[
					'class' => $data['interface'] ? 'EDITOR' : ''
				]
			)
		]
	).
	h::{'input[type=hidden][name=id]'}(
		[
			'value' => $id
		]
	).
	h::{'input[type=hidden][name=mode][value=edit_page]'}()
);
