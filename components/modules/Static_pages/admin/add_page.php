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
$Route = Route::instance();
$Index = Index::instance();
$L     = Language::instance();
Page::instance()->title($L->adding_of_page);
$Index->cancel_button_back = true;
$Index->action             = 'admin/Static_pages';
$Index->content(
	h::{'h2.cs-center'}(
		$L->adding_of_page
	).
	h::{'cs-table[center][with-header] cs-table-row| cs-table-cell'}(
		[
			$L->category,
			$L->page_title,
			h::info('page_path'),
			h::info('page_interface')
		],
		[
			h::{'select[name=category][size=5]'}(
				get_categories_list(),
				[
					'selected' => isset($Route->route[1]) ? (int)$Route->route[1] : 0
				]
			),
			h::{'input[name=title]'}(),
			h::{'input[name=path]'}(),
			h::{'div radio[name=interface]'}(
				[
					'checked' => 1,
					'value'   => [0, 1],
					'in'      => [$L->off, $L->on]
				]
			)
		]
	).
	h::{'cs-table[center][with-header] cs-table-row| cs-table-cell'}(
		[
			$L->content,
			h::{'textarea.EDITOR[name=content]'}()
		]
	).
	h::{'input[type=hidden][name=mode][value=add_page]'}()
);
