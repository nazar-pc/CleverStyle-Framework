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
global $Index, $Page, $L, $User, $db, $Config;
$Page->title($L->drafts);
$module					= path($L->Blogs);
$Index->form			= true;
$Index->buttons			= false;
$Index->form_atributes	= ['class'	=> ''];
$page					= isset($Config->route[1]) ? (int)$Config->route[1] : 1;
$page					= $page > 0 ? $page : 1;
if ($page > 1) {
	$Page->title($L->blogs_nav_page($page));
}
$num					= $Config->module('Blogs')->posts_per_page;
$from					= ($page - 1) * $num;
$cdb					= $db->{$Config->module('Blogs')->db('posts')};
$posts_count			= $cdb->qfs([
	"SELECT COUNT(`id`)
	FROM `[prefix]blogs_posts`
	WHERE
		`draft` = 1 AND
		`user`	= '%s'
	ORDER BY `date` DESC
	LIMIT $from, $num",
	$User->id
]);
$posts					= $cdb->qfas([
	"SELECT `id`
	FROM `[prefix]blogs_posts`
	WHERE
		`draft` = 1 AND
		`user`	= '%s'
	ORDER BY `date` DESC
	LIMIT $from, $num",
	$User->id
]);
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
		$posts ? h::{'nav.cs-center'}(
			pages(
				$page,
				ceil($posts_count / $num),
				function ($page) use ($module, $L) {
					return $page == 1 ? $module.'/'.path($L->drafts) : $module.'/'.path($L->drafts).'/'.$page;
				},
				true
			)
		) : ''
	)
);