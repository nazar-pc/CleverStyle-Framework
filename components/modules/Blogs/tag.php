<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			\h;
global $Index, $Blogs, $Page, $L, $User, $db, $Config;
$rc						= array_slice($Config->routing['current'], 1);
if (!isset($rc[0])) {
	define('ERROR_PAGE', 404);
}
$module					= path($L->{MODULE});
if ($User->is('user')) {
	if ($User->is('admin') && $User->get_user_permission('admin/'.MODULE, 'index')) {
		$Index->content(
			h::{'a.cs-button-compact'}(
				h::icon('wrench'),
				[
					'href'			=> 'admin/'.MODULE,
					'data-title'	=> $L->administration
				]
			)
		);
	}
	$Index->content(
		h::{'a.cs-button-compact'}(
			h::icon('document'),
			[
				'href'			=> $module.'/new_post',
				'data-title'	=> $L->new_post
			]
		).
		h::br()
	);
}
$Index->form			= true;
$Index->buttons			= false;
$Index->form_atributes	= ['class'	=> ''];
$page					= isset($rc[1]) ? (int)$rc[1] : 1;
$page					= $page > 0 ? $page : 1;
if ($page > 1) {
	$Page->title($L->blog_nav_page($page));
}
$num					= $Config->module(MODULE)->get('posts_per_page');
$from					= ($page - 1) * $num;
$cdb					= $db->{$Config->module(MODULE)->db('posts')};
$tag					= $cdb->qf(
	[
		"SELECT `id`
		FROM `[prefix]blogs_tags`
		WHERE `text` = '%s'
		LIMIT 1",
		$rc[0]
	],
	true
);
if (!$tag) {
	$tag					= $cdb->qf(
		[
			"SELECT `t`.`id`
			FROM `[prefix]blogs_tags` AS `t` RIGHT OUTER JOIN `[prefix]texts_data` AS `d`
			ON `t`.`text` = `d`.`id_`
			WHERE
				`t`.`text` = '%1\$s' OR
				(
					`d`.`text` = '%1\$s' AND `d`.`lang` = '%2\$s'
				)
			LIMIT 1",
			$rc[0],
			$L->clang
		],
		true
	);
}
if (!$tag) {
	define('ERROR_PAGE', 404);
}
$tag					= [
	'id'	=> $tag,
	'text'	=> $Blogs->get_tag($tag)
];;
$Page->title($tag['text']);
$Page->title($L->latest_posts);
$Page->Keywords			= keywords($L->{MODULE}.' '.$tag['text'].' '.$L->latest_posts).', '.$Page->Keywords;
$Page->Description		= description($L->{MODULE}.' - '.$tag['text'].' - '.$L->latest_posts.'. '.$Page->Description);
$posts_count			= $cdb->qf(
	[
		"SELECT COUNT(`id`)
		FROM `[prefix]blogs_posts_tags`
		WHERE `tag` = '%s'",
		$tag['id'],
	],
	true
);
$posts					= $cdb->qfa(
	[
		"SELECT `id`
			FROM `[prefix]blogs_posts_tags`
			WHERE `tag` = '%s'
			ORDER BY `date` DESC
			LIMIT $from, $num",
		$tag['id'],
	],
	true
);
if (empty($posts)) {
	$Index->content(
		h::{'p.cs-center'}($L->no_posts_yet)
	);
}
$Index->content(
	h::{'section.cs-blogs-post-latest'}(
		get_posts_list($posts, $module)
	).
	(
		$posts ? h::{'nav.cs-center'}(
			pages(
				$page,
				ceil($posts_count/$num),
				function ($page) use ($module, $L) {
					return $page == 1 ? $module.'/'.path($L->latest_posts) : $module.'/'.path($L->latest_posts).'/'.$page;
				},
				true
			)
		) : ''
	)
);