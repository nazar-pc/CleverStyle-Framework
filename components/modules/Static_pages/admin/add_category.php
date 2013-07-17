<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
$Config						= Config::instance();
$Index						= Index::instance();
$L							= Language::instance();
Page::instance()->title($L->addition_of_page_category);
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/OAuth2';
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->addition_of_page_category
	).
	h::{'table.cs-fullwidth-table.cs-center-all tr'}(
		h::{'th.ui-widget-header.ui-corner-all'}(
			$L->parent_category,
			$L->category_title,
			h::info('category_path')
		),
		h::{'td.ui-widget-content.ui-corner-all'}(
			h::{'select[name=parent][size=5]'}(
				get_categories_list(),
				[
					'selected'	=> isset($Config->route[1]) ? (int)$Config->route[1] : 0
				]
			),
			h::{'input[name=title]'}(),
			h::{'input[name=path]'}()
		)
	).
	h::{'input[type=hidden][name=mode][value=add_category]'}()
);