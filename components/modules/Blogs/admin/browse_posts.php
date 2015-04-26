<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route;
$Config			= Config::instance();
$Index			= Index::instance();
$L				= Language::instance();
$Index->buttons	= false;
Page::instance()->title($L->browse_posts);
$Route			= Route::instance();
$page			= isset($Route->route[1]) ? (int)$Route->route[1] : 1;
$page			= $page > 0 ? $page : 1;
$total			= Posts::instance()->get_total_count();
$Index->content(
	h::{'cs-table[center][list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
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
		h::{'cs-table-row| cs-table-cell'}(
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
