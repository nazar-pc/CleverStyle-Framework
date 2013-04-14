<?php
/**
 * @package		Blogs
 * @category	modules
 * @version		0.002
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			h;
global $Index, $Page, $L, $Config;
$Index->title_auto	= false;
$Page->title($L->administration);
$Page->title($L->{MODULE});
$Page->css([
	'components/modules/'.MODULE.'/includes/css/admin.css',
	'components/modules/'.MODULE.'/includes/css/general.css'
]);
$Page->js([
	'components/modules/'.MODULE.'/includes/js/general.js'
]);
$rc					= $Config->route;
$Page->menumore		= h::a(
	[
		$L->general,
		[
			'href'	=> 'admin/'.MODULE,
			'class'	=> !isset($rc[0]) || $rc[0] == 'general' ? 'active' : false
		]
	],
	[
		$L->browse_sections,
		[
			'href'	=> 'admin/'.MODULE.'/browse_sections',
			'class'	=> isset($rc[0]) && $rc[0] == 'browse_sections' ? 'active' : false
		]
	],
	[
		$L->browse_posts,
		[
			'href'	=> 'admin/'.MODULE.'/browse_posts',
			'class'	=> isset($rc[0]) && $rc[0] == 'browse_posts' ? 'active' : false
		]
	]
);
function get_sections_rows ($structure = null, $level = 0, &$content = null) {
	global $L;
	$root		= false;
	$module		= path($L->{MODULE});
	if ($structure === null) {
		global $Blogs;
		$structure			= $Blogs->get_sections_structure();
		$structure['title']	= $L->root_section;
		$root				= true;
		$content			= [];
	}
	$content[]	= [
		[
			h::a(
				$structure['title'].
				h::{'span.ui-priority-primary.cs-blogs-posts-count'}(
					(empty($structure['sections']) ? ' '.$structure['posts'] : ''),
					[
						'data-title'	=> $L->posts_in_section
					]
				),
				[
					'href'	=> $module.(isset($structure['full_path']) ? '/'.path($L->section).'/'.$structure['full_path'] : '')
				]
			),
			[
				'class'	=> 'cs-blogs-padding-left-'.$level
			]
		],
		h::{'a.cs-button-compact'}(
			[
				h::icon('plus'),
				[
					'href'			=> 'admin/'.MODULE.'/add_section/'.$structure['id'],
					'data-title'	=> $L->add_subsection
				]
			]
		).
		(!$root ? h::{'a.cs-button-compact'}(
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
	];
	if (!empty($structure['sections'])) {
		foreach ($structure['sections'] as $section) {
			get_sections_rows($section, $level+1, $content);
		}
	}
	return [$content];
}
function get_sections_select_post (&$disabled, $current = null, $structure = null, $level = 0) {
	$list	= [
		'in'	=> [],
		'value'	=> []
	];
	if ($structure === null) {
		global $Blogs, $L;
		$structure			= $Blogs->get_sections_structure();
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
		$disabled[]			= $structure['id'];
		foreach ($structure['sections'] as $section) {
			$tmp			= get_sections_select_post($disabled, $current, $section, $level+1);
			$list['in']		= array_merge($list['in'], $tmp['in']);
			$list['value']	= array_merge($list['value'], $tmp['value']);
		}
	}
	return $list;
}
function get_sections_select_section ($current = null, $structure = null, $level = 0) {
	$list	= [
		'in'	=> [],
		'value'	=> []
	];
	if ($structure === null) {
		global $Blogs, $L;
		$structure			= $Blogs->get_sections_structure();
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
			$tmp			= get_sections_select_section($current, $section, $level+1);
			$list['in']		= array_merge($list['in'], $tmp['in']);
			$list['value']	= array_merge($list['value'], $tmp['value']);
		}
	}
	return $list;
}
function get_posts_rows ($page = 1) {
	global $db, $Config, $Blogs, $L, $User;
	$module		= path($L->{MODULE});
	$page		= (int)$page ?: 1;
	$page		= $page > 0 ? $page : 1;
	$num		= $Config->module(MODULE)->posts_per_page;
	$from		= ($page - 1) * $num;
	$cdb		= $db->{$Config->module(basename(MODULE))->db('posts')};
	$posts		= $cdb->qfas(
		"SELECT `id`
		FROM `[prefix]blogs_posts`
		ORDER BY `id` DESC
		LIMIT $from, $num"
	);
	$content	= [];
	if ($posts) {
		foreach ($posts as $post) {
			$post		= $Blogs->get($post);
			foreach ($post['sections'] as &$section) {
				$section	= $section ? $Blogs->get_section($section) : [
					'title'	=> $L->root_section
				];
				$section	= h::a(
					$section['title'],
					[
						'href'	=> $module.(isset($section['full_path']) ? '/'.path($L->section).'/'.$section['full_path'] : '')
					]
				);
			}
			unset($section);
			$content[]	= [
				h::a(
					$post['title'],
					[
						'href'	=> $module.'/'.$post['path'].':'.$post['id']
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
									'href'	=> $module.'/'.path($L->tag).'/'.$tag
								]
							);
						},
						$Blogs->get_tag($post['tags'])
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
				h::{'a.cs-button-compact'}(
					[
						h::icon('wrench'),
						[
							'href'			=> 'admin/'.MODULE.'/edit_post/'.$post['id'],
							'data-title'	=> $L->edit
						]
					],
					[
						h::icon('trash'),
						[
							'href'			=> 'admin/'.MODULE.'/delete_post/'.$post['id'],
							'data-title'	=> $L->delete
						]
					]
				)
			];
		}
	}
	return $content;
}
