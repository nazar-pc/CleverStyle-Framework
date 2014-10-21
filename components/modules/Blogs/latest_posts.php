<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			h,
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

$Config					= Config::instance();
$Index					= Index::instance();
$L						= Language::instance();
$Page					= Page::instance();
$Page->title($L->latest_posts);
$module					= path($L->Blogs);
/**
 * Show administration, new post, draft actions
 */
head_actions();
$Index->form			= true;
$Index->buttons			= false;
$page					= isset($Config->route[1]) ? (int)$Config->route[1] : 1;
$page					= $page > 0 ? $page : 1;
$Page->canonical_url($Config->base_url()."/$module/".path($L->latest_posts).($page > 1 ? "/$page" : ''));
Meta::instance()->blog();
if ($page > 1) {
	$Page->title($L->blogs_nav_page($page));
}
$num					= $Config->module('Blogs')->posts_per_page;
$from					= ($page - 1) * $num;
$cdb					= DB::instance()->{$Config->module('Blogs')->db('posts')};
$posts					= $cdb->qfas(
	"SELECT `id`
	FROM `[prefix]blogs_posts`
	WHERE `draft` = 0
	ORDER BY `date` DESC
	LIMIT $from, $num"
);
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
				ceil(Blogs::instance()->get_total_count() / $num),
				function ($page) use ($module, $L) {
					return $page == 1 ? "$module/".path($L->latest_posts) : "$module/".path($L->latest_posts)."/$page";
				},
				true
			)
		) : ''
	)
);
