<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\User;

if (!Event::instance()->fire('Blogs/drafts')) {
	return;
}
$Config = Config::instance();
$Index  = Index::instance();
$L      = Language::instance();
$Page   = Page::instance();
$Posts  = Posts::instance();
$Route  = Route::instance();
$User   = User::instance();
$Page->title($L->drafts);
/**
 * Determine current page
 */
$page = max(
	isset($Route->ids[0]) ? array_slice($Route->ids, -1)[0] : 1,
	1
);
/**
 * If this is not first page - show that in page title
 */
if ($page > 1) {
	$Page->title($L->blogs_nav_page($page));
}
/**
 * Get posts for current page in JSON-LD structure format
 */
$posts_per_page = $Config->module('Blogs')->posts_per_page;
$posts          = $Posts->get_drafts($User->id, $page, $posts_per_page);
/**
 * Render posts page
 */
if (!$posts) {
	$Index->content(
		h::{'p.cs-center'}($L->no_posts_yet)
	);
	return;
}
$posts_count = $Posts->get_drafts_count($User->id);
/**
 * Base url (without page number)
 */
$base_url = $Config->base_url().'/'.path($L->Blogs).'/'.path($L->drafts);
$Index->content(
	Helpers::posts_list(
		$posts,
		$posts_count,
		$page,
		$base_url
	)
);
