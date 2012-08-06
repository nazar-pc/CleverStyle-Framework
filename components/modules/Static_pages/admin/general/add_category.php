<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			\h;
global $Index, $L, $Page;
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Page->title($L->adding_page_category);
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages'}(
		$L->adding_page_category
	).
	h::{'table.cs-fullwidth-table.cs-center-all'}(
		h::{'tr th.ui-widget-header.ui-corner-all'}([
			$L->parent_category,
			$L->title,
			h::info('page_path')
		]).
		h::{'tr td.ui-widget-content.ui-corner-all'}([
			h::select(
				get_categories_list(),
				[
					'name'		=> 'parent',
					'size'		=> 5,
					'class'		=> 'cs-form-element'
				]
			),
			h::{'input.cs-form-element'}([
				'name'		=> 'title'
			]),
			h::{'input.cs-form-element'}([
				'name'		=> 'path'
			])
		])
	).
	h::{'input[type=hidden][name=mode][value=add_category]'}()
);