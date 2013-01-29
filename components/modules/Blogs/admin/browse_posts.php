<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\Blogs;
use			h;
global $Index, $L, $Page, $Blogs, $Config;
$Index->buttons	= false;
$Page->title($L->browse_posts);
$page		= isset($_POST['page']) ? (int)$_POST['page'] : 1;
$page		= $page > 0 ? $page : 1;
$total		= $Blogs->get_total_count();
$Index->content(
	h::{'table.cs-center-all.cs-fullwidth-table'}(
		h::{'tr th.ui-widget-header.ui-corner-all'}(
			[
				$L->post_title,
				[
					'style'	=> 'width: 30%'
				]
			],
			[
				$L->post_sections,
				[
					'style'	=> 'width: 25%'
				]
			],
			[
				$L->post_tags,
				[
					'style'	=> 'width: 20%'
				]
			],
			[
				$L->author_date,
				[
					'style'	=> 'width: 15%'
				]
			],
			$L->action
		).
		h::{'tr| td.ui-widget-content.ui-corner-all'}(
			get_posts_rows($page)
		)
	).
	(
		$total ? h::{'nav.cs-center'}(
			pages(
				$page,
				ceil($total/$Config->module(MODULE)->posts_per_page),
				function ($page) {
					return $page == 1 ? 'admin/'.MODULE.'/browse_posts' : 'admin/'.MODULE.'/browse_posts/'.$page;
				}
			)
		) : ''
	)
);