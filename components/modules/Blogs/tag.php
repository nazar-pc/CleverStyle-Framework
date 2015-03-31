<?php
/**
 * @package        Blogs
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use            h,
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Page\Meta,
	cs\Page,
	cs\Route;

if (!Event::instance()->fire('Blogs/tag')) {
	return;
}

$Config = Config::instance();
$Index  = Index::instance();
$Page   = Page::instance();
$rc     = array_slice(Route::instance()->route, 1);
if (!isset($rc[0])) {
	error_code(404);
	return;
}
$L      = Language::instance();
$module = path($L->Blogs);
/**
 * Show administration, new post, draft actions
 */
head_actions();
$Index->form    = true;
$Index->buttons = false;
$page           = isset($rc[1]) ? (int)$rc[1] : 1;
$page           = $page > 0 ? $page : 1;
$Page->canonical_url($Config->base_url()."/$module/".path($L->tag)."/$rc[0]".($page > 1 ? "/$page" : ''));
Meta::instance()->blog();
if ($page > 1) {
	$Page->title($L->blogs_nav_page($page));
}
$number = $Config->module('Blogs')->posts_per_page;
$Blogs  = Blogs::instance();
$tag    = $Blogs->find_tag($rc[0]);
if (!$tag) {
	error_code(404);
	return;
}
$tag = [
	'id'   => $tag,
	'text' => Blogs::instance()->get_tag($tag)
];
$Page->title($tag['text']);
$Page->title($L->latest_posts);
$Page->atom(
	"Blogs/atom.xml/?tag=$tag[id]",
	implode($Config->core['title_delimiter'], [$L->latest_posts, $L->tag, $tag['text']])
);
$posts_count = $Blogs->get_for_tag_count($tag['id'], $L->clang, $page, $number);
if (!$posts_count) {
	$Index->content(
		h::{'p.cs-center'}($L->no_posts_yet)
	);
	return;
}
$posts = $Blogs->get_for_tag($tag['id'], $L->clang, $page, $number);
$Index->content(
	h::{'section.cs-blogs-post-latest'}(
		get_posts_list($posts)
	).
	(
	$posts ? h::{'div.cs-center-all.uk-margin nav.uk-button-group'}(
		pages(
			$page,
			ceil($posts_count / $number),
			function ($page) use ($module, $L, $rc) {
				return $page == 1 ? "$module/".path($L->tag)."/$rc[0]" : "$module/".path($L->tag)."/$rc[0]/$page";
			},
			true
		)
	) : ''
	)
);
