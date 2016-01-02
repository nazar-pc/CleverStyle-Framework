<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\DB,
	cs\Language,
	cs\User;
function get_sections_rows ($structure = null, $level = 0) {
	$L			= Language::instance();
	$root		= false;
	$module		= path($L->Blogs);
	if ($structure === null) {
		$structure			= Sections::instance()->get_structure();
		$structure['title']	= $L->root_section;
		$root				= true;
	}
	$content	= [];
	$content[]	= [
		[
			h::a(
				$structure['title'].
				h::{'b.cs-blogs-posts-count'}(
					(empty($structure['sections']) ? ' '.$structure['posts'] : ''),
					[
						'tooltip' => $L->posts_in_section
					]
				),
				[
					'href'	=> $module.(isset($structure['full_path']) ? '/'.path($L->section)."/$structure[full_path]" : '')
				]
			),
			[
				'class'	=> "cs-blogs-padding-left-$level"
			]
		],
		h::{'a[is=cs-link-button][icon=plus]'}(
			[
				'href'		=> "admin/Blogs/add_section/$structure[id]",
				'tooltip'	=> $L->add_subsection
			]
		).
		(!$root
			? h::{'a[is=cs-link-button][icon=pencil]'}(
				[
					'href'		=> "admin/Blogs/edit_section/$structure[id]",
					'tooltip'	=> $L->edit
				]
			).
			h::{'a[is=cs-link-button][icon=trash]'}(
				[
					'href'		=> "admin/Blogs/delete_section/$structure[id]",
					'tooltip'	=> $L->delete
				]
			)
			: false
		)
	];
	if (!empty($structure['sections'])) {
		foreach ($structure['sections'] as $section) {
			$content = array_merge($content, get_sections_rows($section, $level + 1));
		}
	}
	return $content;
}
function get_sections_select_section ($current = null, $structure = null, $level = 0) {
	$list	= [
		'in'	=> [],
		'value'	=> []
	];
	if ($structure === null) {
		$structure			= Sections::instance()->get_structure();
		$list['in'][]		= Language::instance()->root_section;
		$list['value'][]	= 0;
	} else {
		if ($structure['id'] == $current) {
			return $list;
		}
		$list['in'][]		= str_repeat('&nbsp;', $level).$structure['title'];
		$list['value'][]	= $structure['id'];
	}
	if (!empty($structure['sections'])) {
		foreach ($structure['sections'] as $section) {
			$tmp			= get_sections_select_section($current, $section, $level+1);
			$list['in']		= array_merge($list['in'], $tmp['in']);
			$list['value']	= array_merge($list['value'], $tmp['value']);
		}
	}
	return $list;
}
function get_posts_rows ($page = 1) {
	$Posts		= Posts::instance();
	$Sections	= Sections::instance();
	$Config		= Config::instance();
	$L			= Language::instance();
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
