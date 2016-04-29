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
	h,
	cs\Config,
	cs\DB,
	cs\Language\Prefix,
	cs\User;

function get_posts_rows ($page = 1) {
	$Posts		= Posts::instance();
	$Sections	= Sections::instance();
	$Config		= Config::instance();
	$L			= new Prefix('blogs_');
	$User		= User::instance();
	$module		= path($L->Blogs);
	$page		= (int)$page ?: 1;
	$page		= $page > 0 ? $page : 1;
	$num		= $Config->module('Blogs')->posts_per_page;
	$from		= ($page - 1) * $num;
	$cdb		= DB::instance()->{$Config->module('Blogs')->db('posts')};
	$posts		= $cdb->qfas(
		"SELECT `id`
		FROM `[prefix]blogs_posts`
		ORDER BY `id` DESC
		LIMIT $from, $num"
	);
	$content	= [];
	if ($posts) {
		foreach ($posts as $post) {
			$post		= $Posts->get($post);
			foreach ($post['sections'] as &$section) {
				$section	= $section ? $Sections->get($section) : [
					'title'	=> $L->root_section
				];
				$section	= h::a(
					$section['title'],
					[
						'href'	=> $module.(isset($section['full_path']) ? '/'.path($L->section)."/$section[full_path]" : '')
					]
				);
			}
			unset($section);
			$content[]	= [
				h::a(
					$post['title'],
					[
						'href'	=> "$module/$post[path]:$post[id]"
					]
				),
				implode(', ', $post['sections']),
				implode(
					', ',
					array_map(
						function ($tag) use ($L, $module) {
							return h::a(
								$tag,
								[
									'href'	=> "$module/".path($L->tag)."/$tag"
								]
							);
						},
						$post['tags']
					)
				),
				h::a(
					$User->username($post['user']),
					[
						'href'	=> 'profile/'.$User->get('login', $post['user'])
					]
				).
				h::br().
				date($L->_datetime, $post['date']),
				h::{'a[is=cs-link-button][icon=pencil]'}(
					[
						'href'		=> "Blogs/edit_post/$post[id]",
						'tooltip'	=> $L->edit
					]
				).
				h::{'a[is=cs-link-button][icon=trash]'}(
					[
						'href'		=> "admin/Blogs/delete_post/$post[id]",
						'tooltip'	=> $L->delete
					]
				)
			];
		}
	}
	return $content;
}
