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
	cs\Page\Meta,
	cs\Page,
	cs\Route;

if (!Event::instance()->fire('Blogs/latest_posts')) {
	return;
}
$Config = Config::instance();
$Index  = Index::instance();
$L      = Language::instance();
$Meta   = Meta::instance();
$Page   = Page::instance();
$Posts  = Posts::instance();
$Route  = Route::instance();
/**
 * Page title
 */
$Page->title($L->latest_posts);
/**
 * Now add link to Atom feed for latest posts
 */
$Page->atom('Blogs/atom.xml', $L->latest_posts);
/**
 * Set page of blog type (Open Graph protocol)
 */
$Meta->blog();
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
$posts          = $Posts->get_latest_posts($page, $posts_per_page);
/**
 * Render posts page
 */
if (!$posts) {
	$Index->content(
		h::{'p.cs-center'}($L->no_posts_yet)
	);
	return;
}
/**
 * Base url (without page number)
 */
$base_url = $Config->base_url().'/'.path($L->Blogs).'/'.path($L->latest_posts);
$Index->content(
	Helpers::posts_list(
		$posts,
		$Posts->get_total_count(),
		$page,
		$base_url
	)
);
