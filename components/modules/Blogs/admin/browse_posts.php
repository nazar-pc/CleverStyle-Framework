<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Language\Prefix,
	cs\Page,
	cs\Request;

$Config  = Config::instance();
$L       = new Prefix('blogs_');
$Page    = Page::instance();
$Request = Request::instance();
$page    = isset($Request->route[1]) ? (int)$Request->route[1] : 1;
$page    = $page > 0 ? $page : 1;
$total   = Posts::instance()->get_total_count();
$Page->title($L->browse_posts);
$Page->content(
	h::{'table.cs-table[center][list]'}(
		h::{'tr th'}(
			[
				$L->post_title,
				[
					'style' => 'width: 30%'
				]
			],
			[
				$L->post_sections,
				[
					'style' => 'width: 25%'
				]
			],
			[
				$L->post_tags,
				[
					'style' => 'width: 20%'
				]
			],
			[
				$L->author_date,
				[
					'style' => 'width: 15%'
				]
			],
			$L->action
		).
		h::{'tr| td'}(
			get_posts_rows($page)
		)
	).
	(
	$total ? h::{'.cs-block-margin.cs-text-center.cs-margin navnav[is=cs-nav-pagination]'}(
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
