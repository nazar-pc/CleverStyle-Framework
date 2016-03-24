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
	cs\Config,
	cs\Event,
	cs\Language\Prefix,
	cs\Page\Meta,
	cs\Page,
	cs\Request;

if (!Event::instance()->fire('Blogs/latest_posts')) {
	return;
}
$Config  = Config::instance();
$L       = new Prefix('blogs_');
$Meta    = Meta::instance();
$Page    = Page::instance();
$Posts   = Posts::instance();
$Request = Request::instance();
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
	isset($Request->route_ids[0]) ? array_slice($Request->route_ids, -1)[0] : 1,
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
 * Base url (without page number)
 */
$base_url = $Config->base_url().'/'.path($L->Blogs).'/'.path($L->latest_posts);
/**
 * Render posts page
 */
Helpers::show_posts_list(
	$posts,
	$Posts->get_total_count(),
	$page,
	$base_url
);
