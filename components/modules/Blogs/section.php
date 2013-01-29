<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			h;
global $Index, $Blogs, $Page, $L, $User, $db, $Config;
$rc						= array_slice($Config->route, 1);
$structure				= $Blogs->get_sections_structure();
$keywords				= [];
$description			= [];
$path					= [];
foreach ($rc as $path_) {
	if ($structure['posts']	== 0 && isset($structure['sections'][$path_])) {
		array_shift($rc);
		$structure		= $structure['sections'][$path_];
		$Page->title($structure['title']);
		$keywords[]		= $structure['title'];
		$description[]	= $structure['title'];
		$path[]			= $path_;
	} else {
		break;
	}
}
unset($path_);
$path					= implode('/', $path);
if (isset($structure['id'])) {
	$section	= $structure['id'];
} else {
	define('ERROR_PAGE', 404);
	return;
}
$Page->title($L->latest_posts);
$Page->Keywords			= keywords($L->{MODULE}.' '.implode(' ', $keywords).' '.$L->latest_posts).', '.$Page->Keywords;
$Page->Description		= description($L->{MODULE}.' - '.implode(' - ', $description).' - '.$L->latest_posts.'. '.$Page->Description);
$Page->og('type', 'blog');
$module					= path($L->{MODULE});
if ($User->user()) {
	if ($User->admin() && $User->get_user_permission('admin/'.MODULE, 'index')) {
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
				'href'			=> $module.'/new_post/'.$section,
				'data-title'	=> $L->new_post
			]
		).
		h::{'a.cs-button'}(
			$L->drafts,
			[
				'href'			=> $module.'/'.path($L->drafts),
				'data-title'	=> $L->drafts,
				'style'			=> 'vertical-align: top;'
			]
		).
		h::br()
	);
}
$Index->form			= true;
$Index->buttons			= false;
$Index->form_atributes	= ['class'	=> ''];
$page					= isset($rc[0]) ? (int)$rc[0] : 1;
$page					= $page > 0 ? $page : 1;
$Page->canonical_url($Config->base_url().'/'.$module.'/'.path($L->section).'/'.$path.($page > 1 ? '/'.$page : ''));
if ($page > 1) {
	$Page->title($L->blogs_nav_page($page));
}
$num					= $Config->module(MODULE)->posts_per_page;
$from					= ($page - 1) * $num;
$cdb					= $db->{$Config->module(MODULE)->db('posts')};
$posts					= $cdb->qfas(
	"SELECT `s`.`id`
	FROM `[prefix]blogs_posts_sections` AS `s`
		LEFT OUTER JOIN `[prefix]blogs_posts` AS `p`
	ON `s`.`id` = `p`.`id`
	WHERE
		`s`.`section`	= $section AND
		`p`.`draft`		= 0
	ORDER BY `p`.`date` DESC
	LIMIT $from, $num"
);
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
		$posts ? h::{'nav.cs-center'}(
			pages(
				$page,
				ceil($structure['posts'] / $num),
				function ($page) use ($module, $L, $path) {
					return $page == 1 ? $module.'/'.path($L->section).'/'.$path : $module.'/'.path($L->section).'/'.$path.'/'.$page;
				},
				true
			)
		) : ''
	)
);