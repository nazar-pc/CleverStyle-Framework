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
global $Index, $L, $Page, $Config;
$Page->title($L->adding_of_page);
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/'.MODULE;
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->adding_of_page
	).
	h::{'table.cs-fullwidth-table.cs-center-all tr'}(
		h::{'th.ui-widget-header.ui-corner-all'}(
			$L->category,
			$L->page_title,
			h::info('page_path'),
			h::info('page_interface')
		),
		h::{'td.ui-widget-content.ui-corner-all'}(
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
		),
		h::{'th.ui-widget-header.ui-corner-all[colspan=4]'}(
			$L->content
		),
		h::{'td.ui-widget-content.ui-corner-all[colspan=4] textarea.cs-wide-textarea.EDITOR[name=content]'}()
	).
	h::{'input[type=hidden][name=mode][value=add_page]'}()
);