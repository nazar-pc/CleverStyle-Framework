<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			h,
			cs\Config,
			cs\DB,
			cs\Index,
			cs\Language,
			cs\Page,
			cs\User;
$Config					= Config::instance();
$Index					= Index::instance();
$L						= Language::instance();
$Page					= Page::instance();
$User					= User::instance();
$Page->title($L->latest_posts);
$Page->Keywords			= keywords("$L->Blogs $L->latest_posts").", $Page->Keywords";
$Page->Description		= description("$L->Blogs - $L->latest_posts. $Page->Description");//TODO og type, description and keywords
$module					= path($L->Blogs);
if ($User->user()) {
	if ($User->admin() && $User->get_user_permission('admin/Blogs', 'index')) {
		$Index->content(
			h::{'a.cs-button'}(
				h::icon('gears'),
				[
					'href'			=> 'admin/Blogs',
					'data-title'	=> $L->administration
				]
			)
		);
	}
	$Index->content(
		h::{'a.cs-button'}(
			h::icon('pencil').$L->new_post,
			[
				'href'			=> "$module/new_post",
				'data-title'	=> $L->new_post
			]
		).
		h::{'a.cs-button'}(
			h::icon('archive').$L->drafts,
			[
				'href'			=> "$module/".path($L->drafts),
				'data-title'	=> $L->drafts
			]
		).
		h::br()
	);
}
$Index->form			= true;
$Index->buttons			= false;
$Index->form_atributes	= ['class'	=> ''];
$page					= isset($Config->route[1]) ? (int)$Config->route[1] : 1;
$page					= $page > 0 ? $page : 1;
$Page->canonical_url($Config->base_url()."/$module/".path($L->latest_posts).($page > 1 ? "/$page" : ''));
$Page->og('type', 'blog');
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