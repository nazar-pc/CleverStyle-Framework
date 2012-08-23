<?php
/**
 * @package		Blog
 * @category	modules
 * @version		0.002
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blog;
use			\h;
global $Core, $Index, $Page, $L;
$Index->title_auto	= false;
$Page->title($L->administration);
$Page->title($L->{MODULE});
$Page->css('components/modules/'.MODULE.'/includes/css/style.css');
$Page->menumore		= h::a(
	[
		$L->blogs_sections,
		[
			'href'	=> 'admin/'.MODULE
		]
	],
	[
		$L->blogs_posts,
		[
			'href'	=> 'admin/'.MODULE.'/browse_posts'
		]
	]
);
include_once MFOLDER.'/../class.php';
$Core->create('cs\\modules\\Blog\\Blog');
function get_sections_rows ($structure = null, $level = 0, $parent_sections = []) {
	global $L;
	$root					= false;
	if ($structure === null) {
		global $Blog;
		$structure			= $Blog->get_sections_structure();
		$structure['title']	= $L->root_section;
		$root				= true;
	} else {
		$parent_sections[]	= $structure['path'];
	}
	$content				= [[
		[
			h::a(
				$structure['title'].
				h::{'span.ui-priority-primary.cs-blog-posts-count'}(
					(empty($structure['sections']) ? ' '.$structure['posts'] : ''),
					[
						'data-title'	=> $L->posts_in_section
					]
				),
				[
					'href'	=> MODULE.($parent_sections ? '/'.implode('/', $parent_sections) : '')
				]
			),
			[
				'class'	=> 'cs-blog-padding-left-'.$level
			]
		],
		h::{'a.cs-button.cs-button-compact'}(
			[
				h::icon('plus'),
				[
					'href'			=> 'admin/'.MODULE.'/add_section/'.$structure['id'],
					'data-title'	=> $L->add_subsection
				]
			]
		).
		(!$root ? h::{'a.cs-button.cs-button-compact'}(
			[
				h::icon('wrench'),
				[
					'href'			=> 'admin/'.MODULE.'/edit_section/'.$structure['id'],
					'data-title'	=> $L->edit
				]
			],
			[
				h::icon('trash'),
				[
					'href'			=> 'admin/'.MODULE.'/delete_section/'.$structure['id'],
					'data-title'	=> $L->delete
				]
			]
		) : false)
	]];
	if (!empty($structure['sections'])) {
		foreach ($structure['sections'] as $section) {
			$content	= array_merge($content, get_sections_rows($section, $level+1, $parent_sections));
		}
	}
	return $content;
}
function get_sections_list ($current = null, $structure = null, $level = 0) {
	$list	= [
		'in'	=> [],
		'value'	=> []
	];
	if ($structure === null) {
		global $Blog, $L;
		$structure			= $Blog->get_sections_structure();
		$list['in'][]		= $L->root_section;
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
			$tmp			= get_sections_list($current, $section, $level+1);
			$list['in']		= array_merge($list['in'], $tmp['in']);
			$list['value']	= array_merge($list['value'], $tmp['value']);
		}
	}
	return $list;
}
function get_posts_rows ($page	= 1) {
	global $db, $Config, $Blog;
	$page		= (int)$page ?: 1;
	$from		= --$page*50;
	$cdb		= $db->{$Config->module(basename(MODULE))->db('posts')};
	$posts		= $cdb->qfa(
		"SELECT `id`
		FROM `[prefix]blog_posts`
		LIMIT $from, 50",
		true
	);
	$content	= [];
	if ($posts) {
		foreach ($posts as $post) {
			$post		= $Blog->get($post);
			foreach ($post['sections'] as &$section) {
				$section	= $Blog->get_section($section)['title'];
			}
			unset($section);
			$tags_list	= $Blog->get_tags_list();
			foreach ($post['tags'] as &$tag) {
				$tag		= $tags_list[$tag];
			}
			unset($tag);
			$content[]	= [
				$post['title'],
				implode(', ', $post['sections']),
				implode(', ', $post['tags']),
				''
			];
		}
	}
	return $content;
}
