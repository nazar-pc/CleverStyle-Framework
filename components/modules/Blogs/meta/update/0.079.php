<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Config	= Config::instance();
/**
 *	@var \cs\DB\_Abstract $cdb
 */
$cdb	= DB::instance()->{$Config->module('Blogs')->db('posts')}();
$tags	= $cdb->qfa(
	"SELECT `id`, `tag`, `lang`
	FROM `[prefix]blogs_posts_tags`
	GROUP BY `id`, `tag`, `lang`"
);
foreach ($tags as $t) {
	$cdb->q(
		"DELETE FROM `[prefix]blogs_posts_tags`
		WHERE
			`id`	= '%s' AND
			`tag`	= '%s' AND
			`lang`	= '%s'",
		$t['id'],
		$t['tag'],
		$t['lang']
	);
	$cdb->q(
		"INSERT INTO `[prefix]blogs_posts_tags`
			(`id`, `tag`, `lang`)
		VALUES
			('%s', '%s', '%s')",
		$t['id'],
		$t['tag'],
		$t['lang']
	);
}
unset(Cache::instance()->Blogs);
