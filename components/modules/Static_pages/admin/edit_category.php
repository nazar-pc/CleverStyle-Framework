<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h;
global $Index, $L, $Page, $Static_pages, $Config;
$id							= (int)$Config->route[1];
$data						= $Static_pages->get_category($id);
$Page->title($L->editing_of_page_category($data['title']));
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/'.MODULE;
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->editing_of_page_category($data['title'])
	).
	h::{'table.cs-fullwidth-table.cs-center-all tr'}(
		h::{'th.ui-widget-header.ui-corner-all'}(
			$L->parent_category,
			$L->category_title,
			h::info('category_path')
		),
		h::{'td.ui-widget-content.ui-corner-all'}(
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
		)
	).
	h::{"input[type=hidden][name=id][value=$id]"}().
	h::{'input[type=hidden][name=mode][value=edit_category]'}()
);