<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use
	h,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route;
$Index						= Index::instance();
$L							= Language::instance();
$id							= (int)Route::instance()->route[1];
$data						= Static_pages::instance()->get_category($id);
Page::instance()->title($L->editing_of_page_category($data['title']));
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/Static_pages';
$Index->content(
	h::{'h2.cs-center'}(
		$L->editing_of_page_category($data['title'])
	).
	h::{'cs-table[center][with-header] cs-table-row| cs-table-cell'}(
		[
			$L->parent_category,
			$L->category_title,
			h::info('category_path')
		],
		[
			h::{'select[name=parent][size=5]'}(
				get_categories_list($id),
				[
					'selected'	=> $data['parent']
				]
			),
			h::{'input[name=title]'}([
				'value'	=> $data['title']
			]),
			h::{'input[name=path]'}([
				'value'	=> $data['path']
			])
		]
	).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $id
	]).
	h::{'input[type=hidden][name=mode][value=edit_category]'}()
);
