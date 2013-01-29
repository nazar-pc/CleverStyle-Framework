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
global $Index, $L, $Config;
$rc				= $Config->route;
$Index->buttons	= false;
$Index->content(
	h::{'table.cs-left-all.cs-fullwidth-table'}(
		h::{'tr th.ui-widget-header.ui-corner-all'}(
			[
				$L->page_title,
				[
					'style'	=> 'width: 80%'
				]
			],
			$L->action
		).
		h::{'tr| td.ui-widget-content.ui-corner-all'}(
			get_pages_rows()
		).
		h::{'tr td[colspan=2] a.cs-button'}(
			[
				$L->add_page,
				[
					'href'	=> 'admin/'.MODULE.'/add_page/'.array_slice($rc, -1)[0]
				]
			]/*,
			[
				$L->add_page_live,
				[
					'href'	=> 'admin/'.MODULE.'/add_page_live/'.array_slice($rc, -1)[0]
				]
			]*/
		)
	)
);