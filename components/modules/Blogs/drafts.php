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
			cs\Page,
			cs\User;
$Config					= Config::instance();
$module_data			= $Config->module('Blogs');
$Index					= Index::instance();
$L						= Language::instance();
$Page					= Page::instance();
$User					= User::instance();
if (!$User->admin() && $module_data->new_posts_only_from_admins) {
	error_code(403);
	return;
}
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
$num					= $module_data->posts_per_page;
$from					= ($page - 1) * $num;
$cdb					= DB::instance()->{$module_data->db('posts')};
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
		$posts ? h::{'div.cs-center-all.uk-margin nav.uk-button-group'}(
			pages(
				$page,
				ceil($posts_count / $num),
				function ($page) use ($module, $L) {
					return $page == 1 ? "$module/".path($L->drafts) : "$module/".path($L->drafts)."/$page";
				},
				true
			)
		) : ''
	)
);
