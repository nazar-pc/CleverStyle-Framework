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
			cs\Page;
$Config					= Config::instance();
$Index					= Index::instance();
$Page					= Page::instance();
$rc						= array_slice($Config->route, 1);
if (!isset($rc[0])) {
	error_code(404);
	return;
}
$L						= Language::instance();
$module					= path($L->Blogs);
/**
 * Show administration, new post, draft actions
 */
head_actions();
$Index->form			= true;
$Index->buttons			= false;
$Index->form_atributes	= ['class'	=> ''];
$page					= isset($rc[1]) ? (int)$rc[1] : 1;
$page					= $page > 0 ? $page : 1;
$Page->canonical_url($Config->base_url()."/$module/".path($L->tag)."/$rc[0]".($page > 1 ? "/$page" : ''));
$Page->og('type', 'blog');
if ($page > 1) {
	$Page->title($L->blogs_nav_page($page));
}
$num					= $Config->module('Blogs')->posts_per_page;
$from					= ($page - 1) * $num;
$cdb					= DB::instance()->{$Config->module('Blogs')->db('posts')};
$tag					= $cdb->qfs([
	"SELECT `id`
	FROM  `[prefix]blogs_tags`
	WHERE `text` = '%s'
	LIMIT 1",
	trim(xap($rc[0]))
]);
if (!$tag) {
	error_code(404);
	return;
}
$tag					= [
	'id'	=> $tag,
	'text'	=> Blogs::instance()->get_tag($tag)
];;
$Page->title($tag['text']);
$Page->title($L->latest_posts);
$posts_count			= $cdb->qfs([
	"SELECT COUNT(`t`.`id`)
	FROM `[prefix]blogs_posts_tags` AS `t`
		LEFT JOIN `[prefix]blogs_posts` AS `p`
	ON `t`.`id` = `p`.`id`
	WHERE
		`t`.`tag`	= '%s' AND
		`p`.`draft`	= 0 AND
		`t`.`lang`	= '%s'",
	$tag['id'],
	$L->clang
]);
$posts					= $cdb->qfas([
	"SELECT `t`.`id`
	FROM `[prefix]blogs_posts_tags` AS `t`
		LEFT JOIN `[prefix]blogs_posts` AS `p`
	ON `t`.`id` = `p`.`id`
	WHERE
		`t`.`tag`	= '%s' AND
		`p`.`draft`	= 0 AND
		`t`.`lang`	= '%s'
	ORDER BY `p`.`date` DESC
	LIMIT $from, $num",
	$tag['id'],
	$L->clang
]);
if (empty($posts)) {
	$Index->content(
		h::{'p.cs-center'}($L->no_posts_yet)
	);
	return;
}
$Index->content(
	h::{'section.cs-blogs-post-latest'}(
		get_posts_list($posts)
	).
	(
		$posts ? h::{'div.cs-center-all.uk-margin nav.uk-button-group'}(
			pages(
				$page,
				ceil($posts_count / $num),
				function ($page) use ($module, $L, $rc) {
					return $page == 1 ? "$module/".path($L->tag)."/$rc[0]" : "$module/".path($L->tag)."/$rc[0]/$page";
				},
				true
			)
		) : ''
	)
);
