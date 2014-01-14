<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
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
Page::instance()->title($L->adding_of_page);
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/Static_pages';
$Index->content(
	h::{'p.lead.cs-center'}(
		$L->adding_of_page
	).
	h::{'table.cs-table-borderless.cs-center-all'}(
		h::{'thead tr th'}(
			$L->category,
			$L->page_title,
			h::info('page_path'),
			h::info('page_interface')
		),
		h::{'tbody tr td'}(
			h::{'select[name=category][size=5]'}(
				get_categories_list(),
				[
					'selected'	=> isset($Config->route[1]) ? (int)$Config->route[1] : 0
				]
			),
			h::{'input[name=title]'}(),
			h::{'input[name=path]'}(),
			h::{'div input[type=radio][name=interface]'}([
				'checked'	=> 1,
				'value'		=> [0, 1],
				'in'		=> [$L->off, $L->on]
			])
		)
	).
	h::{'table.cs-table-borderless.cs-center-all'}(
		h::{'thead tr th'}(
			$L->content
		),
		h::{'tbody tr td textarea.EDITOR[name=content]'}()
	).
	h::{'input[type=hidden][name=mode][value=add_page]'}()
);
