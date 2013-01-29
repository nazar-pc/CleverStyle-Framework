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
global $Index, $L;
$Index->buttons	= false;
$Index->content(
	h::{'table.cs-left-all.cs-fullwidth-table'}(
		h::{'tr th.ui-widget-header.ui-corner-all'}(
			[
				$L->pages_category,
				[
					'style'	=> 'width: 80%'
				]
			],
			$L->action
		).
		h::{'tr| td.ui-widget-content.ui-corner-all'}(
			get_categories_rows()
		).
		h::{'tr td[colspan=2] a.cs-button'}(
			[
				$L->add_category,
				[
					'href'	=> 'admin/'.MODULE.'/add_category'
				]
			],
			[
				$L->add_page,
				[
					'href'	=> 'admin/'.MODULE.'/add_page'
				]
			]/*,
			[
				$L->add_page_live,
				[
					'href'	=> 'admin/'.MODULE.'/add_page_live'
				]
			]*/
		)
	).
	h::{'p.cs-left'}($L->index_page_path)
);