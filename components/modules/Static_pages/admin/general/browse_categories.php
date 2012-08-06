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
global $Index, $L;
$Index->apply_button	= false;
$Index->reset_button	= false;
$Index->post_buttons	= h::button(
	$L->reset,
	[
	'onclick'	=> 'location.reload()'
	]
);
$Index->content(
	h::{'table.cs-left-all.cs-fullwidth-table'}(
		h::{'tr th.ui-widget-header.ui-corner-all'}(
			$L->pages_category,
			$L->action
		).
		h::{'tr| td.ui-widget-content.ui-corner-all'}(
			get_categories_rows()
		).
		h::{'tr td[colspan=2]'}(
			h::{'a.cs-button'}(
				[
					$L->add_category,
					[
						'href'	=> 'admin/'.MODULE.'/general/add_category'
					]
				],
				[
					$L->add_page,
					[
						'href'	=> 'admin/'.MODULE.'/general/add_page'
					]
				],
				[
					$L->add_page_live,
					[
						'href'	=> 'admin/'.MODULE.'/general/add_page_live'
					]
				]
			)
		)
	)
);