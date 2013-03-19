<?php
/**
 * @package		  Static Pages
 * @category		 modules
 * @version		  0.001
 * @author			Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright		Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		  MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h;
global $Index, $Page, $L, $Config;
$Index->title_auto	= false;
$Page->title($L->administration);
$Page->title($L->{MODULE});
$Page->css('components/modules/'.MODULE.'/includes/css/style.css');
$Page->menumore		= h::a(
	$L->browse_page_categories,
	[
		'href'	=> 'admin/'.MODULE,
		'class'	=> !isset($Config->route[0]) || $Config->route[0] == 'browse_sections' ? 'active' : false
	]
);
function get_categories_rows ($structure = null, $level = 0, $parent_categories = []) {
	global $L;
	$root					= false;
	if ($structure === null) {
		global $Static_pages;
		$structure			= $Static_pages->get_structure();
		$structure['title']	= $L->root_category;
		$root				= true;
	}
	$parent_categories[]	= $structure['id'];
	$content				= [[
		[
			h::a(
				$structure['title'].
				h::{'span.ui-priority-primary.cs-static-pages-count'}(
					count($structure['pages']),
					[
						'data-title'	=> $L->pages_in_category
					]
				),
				[
					'href'	=> 'admin/'.MODULE.'/browse_pages/'.implode('/', $parent_categories)
				]
			),
			[
				'class'	=> 'cs-static-pages-padding-left-'.$level
			]
		],
		h::{'a.cs-button-compact'}(
			[
				h::icon('plus'),
				[
					'href'			=> 'admin/'.MODULE.'/add_category/'.$structure['id'],
					'data-title'	=> $L->add_subcategory
				]
			],
			[
				h::icon('document-b'),
				[
					'href'			=> 'admin/'.MODULE.'/add_page/'.$structure['id'],
					'data-title'	=> $L->add_page
				]
			]/*,
			[
				h::icon('document'),
				[
					'href'			=> 'admin/'.MODULE.'/add_page_live/'.$structure['id'],
					'data-title'	=> $L->add_page_live
				]
			]*/
		).
		(!$root ? h::{'a.cs-button-compact'}(
			[
				h::icon('wrench'),
				[
					'href'			=> 'admin/'.MODULE.'/edit_category/'.$structure['id'],
					'data-title'	=> $L->edit
				]
			],
			[
				h::icon('trash'),
				[
					'href'			=> 'admin/'.MODULE.'/delete_category/'.$structure['id'],
					'data-title'	=> $L->delete
				]
			]
		) : false)
	]];
	if (!empty($structure['categories'])) {
		foreach ($structure['categories'] as $category) {
			$content	= array_merge($content, get_categories_rows($category, $level+1, $parent_categories));
		}
	}
	return [$content];
}
function get_categories_list ($current = null, $structure = null, $level = 0) {
	$list	= [
		'in'	=> [],
		'value'	=> []
	];
	if ($structure === null) {
		global $Static_pages, $L;
		$structure			= $Static_pages->get_structure();
		$list['in'][]		= $L->root_category;
		$list['value'][]	= 0;
	} else {
		if ($structure['id'] == $current) {
			return $list;
		}
		$list['in'][]		= str_repeat('&nbsp;', $level).$structure['title'];
		$list['value'][]	= $structure['id'];
	}
	if (!empty($structure['categories'])) {
		foreach ($structure['categories'] as $category) {
			$tmp			= get_categories_list($current, $category, $level+1);
			$list['in']		= array_merge($list['in'], $tmp['in']);
			$list['value']	= array_merge($list['value'], $tmp['value']);
		}
	}
	return $list;
}
function get_pages_rows () {
	global $Config, $Static_pages, $L, $Page;
	$categories	= array_slice($Config->route, 2);
	$structure	= $Static_pages->get_structure();
	$path		= [];
	if (!empty($categories)) {
		foreach ($categories as $category) {
			$category	= $Static_pages->get_category($category)['path'];
			if (isset($structure['categories'][$category])) {
				$structure	= $structure['categories'][$category];
				$path[]		= $structure['path'];
			}
		}
		unset($category);
	}
	$Page->title($structure['id'] == 0 ? $L->root_category : $structure['title']);
	$path		= !empty($path) ? implode('/', $path).'/' : '';
	$content	= [];
	if (!empty($structure['pages'])) {
		foreach ($structure['pages'] as &$page) {
			$page			= $Static_pages->get($page);
			$content[]		= [
				[
					h::a(
						$page['title'],
						[
							'href'	=> $path.$page['path']
						]
					),
					[
						'class'	=> 'cs-static-pages-padding-left-0'
					]
				],
				h::{'a.cs-button-compact'}(
					[
						h::icon('document-b'),
						[
							'href'			=> 'admin/'.MODULE.'/edit_page/'.$page['id'],
							'data-title'	=> $L->edit
						]
					]/*,
					$page['interface'] ? [
						h::icon('document'),
						[
							'href'			=> 'admin/'.MODULE.'/edit_page_live/'.$page['id'],
							'data-title'	=> $L->edit_page_live
						]
					] : false*/,
					[
						h::icon('trash'),
						[
							'href'			=> 'admin/'.MODULE.'/delete_page/'.$page['id'],
							'data-title'	=> $L->delete
						]
					]
				)
			];
		}
	}
	return [$content];
}