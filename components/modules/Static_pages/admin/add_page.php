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

$Route = Route::instance();
$L     = Language::instance();
Page::instance()->title($L->adding_of_page)
	->content(
		h::{'form[is=cs-form][action=admin/Static_pages]'}(
			h::h2(
				$L->adding_of_page
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
							'selected' => isset($Route->route[1]) ? (int)$Route->route[1] : 0
						]
					),
					h::{'input[is=cs-input-text][full-width][name=title]'}(),
					h::{'input[is=cs-input-text][full-width][name=path]'}(),
					h::{'div radio[name=interface]'}(
						[
							'checked' => 1,
							'value'   => [0, 1],
							'in'      => [$L->off, $L->on]
						]
					)
				)
			).
			h::{'table.cs-table[center] tr'}(
				h::th($L->content),
				h::{'td cs-editor textarea[cs-textarea][autosize][name=content]'}()
			).
			h::p(
				h::{'button[is=cs-button][type=submit][name=mode][value=add_page]'}(
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
