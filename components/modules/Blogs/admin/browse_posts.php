<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\Blogs;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
$Config			= Config::instance();
$Index			= Index::instance();
$L				= Language::instance();
$Index->buttons	= false;
Page::instance()->title($L->browse_posts);
$page			= isset($Config->route[1]) ? (int)$Config->route[1] : 1;
$page			= $page > 0 ? $page : 1;
$total			= Blogs::instance()->get_total_count();
$Index->content(
	h::{'table.cs-center-all.cs-table'}(
		h::{'thead tr th'}(
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
		h::{'tbody tr| td'}(
			get_posts_rows($page)
		)
	).
	(
		$total ? h::{'div.cs-center-all.uk-margin nav.uk-button-group'}(
			pages(
				$page,
				ceil($total / $Config->module('Blogs')->posts_per_page),
				function ($page) {
					return $page == 1 ? 'admin/Blogs/browse_posts' : "admin/Blogs/browse_posts/$page";
				}
			)
		) : ''
	)
);