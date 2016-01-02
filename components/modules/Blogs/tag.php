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
	cs\ExitException,
	cs\Language,
	cs\Page\Meta,
	cs\Page,
	cs\Route;

if (!Event::instance()->fire('Blogs/tag')) {
	return;
}
$Config = Config::instance();
$L      = Language::instance();
$Meta   = Meta::instance();
$Page   = Page::instance();
$Posts  = Posts::instance();
$Tags   = Tags::instance();
$Route  = Route::instance();
/**
 * If no tag specified
 */
if (!isset($Route->route[1])) {
	throw new ExitException(404);
}
/**
 * Find tag
 */
$tag = $Tags->get_by_text($Route->route[1]);
if (!$tag) {
	throw new ExitException(404);
}
$tag = $Tags->get($tag);
/**
 * Add tag to page title
 */
$Page
	->title($tag['text']);
/**
 * Now add link to Atom feed for posts with current tag only
 */
$Page->atom(
	"Blogs/atom.xml/?tag=$tag[id]",
	implode($Config->core['title_delimiter'], [$L->latest_posts, $L->tag, $tag['text']])
);
/**
 * Set page of blog type (Open Graph protocol)
 */
$Meta->blog();
/**
 * Determine current page
 */
$page = max(
	isset($Route->route[2]) ? $Route->route[2] : 1,
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
$posts          = $Posts->get_for_tag($tag['id'], $L->clang, $page, $posts_per_page);
$posts_count    = $Posts->get_for_tag_count($tag['id'], $L->clang);
/**
 * Base url (without page number)
 */
$base_url = $Config->base_url().'/'.path($L->Blogs).'/'.path($L->tag).'/'.$Route->route[1];
/**
 * Render posts page
 */
Helpers::show_posts_list(
	$posts,
	$posts_count,
	$page,
	$base_url
);
