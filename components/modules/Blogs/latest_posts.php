<?php
/**
 * @package        Blogs
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\DB,
	cs\Index,
	cs\Language,
	cs\Page\Meta,
	cs\Page,
	cs\Trigger;

if (!Trigger::instance()->run('Blogs/latest_posts')) {
	return;
}

$Config = Config::instance();
$Index  = Index::instance();
$L      = Language::instance();
$Page   = Page::instance();
$Page->title($L->latest_posts);
$Page->atom('Blogs/atom.xml', $L->latest_posts);
$module = path($L->Blogs);
/**
 * Show administration, new post, draft actions
 */
head_actions();
$Index->form    = true;
$Index->buttons = false;
$page           = isset($Config->route[1]) ? (int)$Config->route[1] : 1;
$page           = $page > 0 ? $page : 1;
$Page->canonical_url($Config->base_url()."/$module/".path($L->latest_posts).($page > 1 ? "/$page" : ''));
Meta::instance()->blog();
if ($page > 1) {
	$Page->title($L->blogs_nav_page($page));
}
$number = $Config->module('Blogs')->posts_per_page;
$Blogs  = Blogs::instance();
$posts  = $Blogs->get_latest_posts($page, $number);
if (empty($posts)) {
	$Index->content(
		h::{'p.cs-center'}($L->no_posts_yet)
	);
}
$Index->content(
	h::{'section.cs-blogs-post-latest'}(
		get_posts_list($posts)
	).
	(
	$posts ? h::{'div.cs-center-all.uk-margin nav.uk-button-group'}(
		pages(
			$page,
			ceil($Blogs->get_total_count() / $number),
			function ($page) use ($module, $L) {
				return $page == 1 ? "$module/".path($L->latest_posts) : "$module/".path($L->latest_posts)."/$page";
			},
			true
		)
	) : ''
	)
);
