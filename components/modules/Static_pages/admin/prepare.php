<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
use
	h,
	cs\Language\Prefix,
	cs\Page,
	cs\Request;

$Page = Page::instance();
$Page->css('components/modules/Static_pages/includes/css/style.css');
function get_categories_rows ($structure = null, $level = 0, $parent_categories = []) {
	$L    = new Prefix('static_pages_');
	$root = false;
	if ($structure === null) {
		$structure          = Pages::instance()->get_structure();
		$structure['title'] = $L->root_category;
		$root               = true;
	}
	$parent_categories[] = $structure['id'];
	$content             = [
		[
			[
				h::a(
					$structure['title'].
					h::{'b.cs-static-pages-count'}(
						count($structure['pages']),
						[
							'tooltip' => $L->pages_in_category
						]
					),
					[
						'href' => 'admin/Static_pages/browse_pages/'.implode('/', $parent_categories)
					]
				),
				[
					'class' => "cs-static-pages-padding-left-$level"
				]
			],
			h::{'a[is=cs-link-button][icon=plus]'}(
				[
					'href'    => "admin/Static_pages/add_category/$structure[id]",
					'tooltip' => $L->add_subcategory
				]
			).
			h::{'a[is=cs-link-button][icon=file-text]'}(
				[
					'href'    => "admin/Static_pages/add_page/$structure[id]",
					'tooltip' => $L->add_page
				]
			).
			(!$root ?
				h::{'a[is=cs-link-button][icon=pencil]'}(
					[
						'href'    => "admin/Static_pages/edit_category/$structure[id]",
						'tooltip' => $L->edit
					]
				).
				h::{'a[is=cs-link-button][icon=trash]'}(
					[
						'href'    => "admin/Static_pages/delete_category/$structure[id]",
						'tooltip' => $L->delete
					]
				)
				: false
			)
		]
	];
	if (!empty($structure['categories'])) {
		foreach ($structure['categories'] as $category) {
			$content = array_merge($content, get_categories_rows($category, $level + 1, $parent_categories));
		}
	}
	return $content;
}

function get_categories_list ($current = null, $structure = null, $level = 0) {
	$list = [
		'in'    => [],
		'value' => []
	];
	if ($structure === null) {
		$structure       = Pages::instance()->get_structure();
		$L               = new Prefix('static_pages_');
		$list['in'][]    = $L->root_category;
		$list['value'][] = 0;
	} else {
		if ($structure['id'] == $current) {
			return $list;
		}
		$list['in'][]    = str_repeat('&nbsp;', $level).$structure['title'];
		$list['value'][] = $structure['id'];
	}
	if (!empty($structure['categories'])) {
		foreach ($structure['categories'] as $category) {
			$tmp           = get_categories_list($current, $category, $level + 1);
			$list['in']    = array_merge($list['in'], $tmp['in']);
			$list['value'] = array_merge($list['value'], $tmp['value']);
		}
	}
	return $list;
}

function get_pages_rows () {
	$L          = new Prefix('static_pages_');
	$Pages      = Pages::instance();
	$Categories = Categories::instance();
	$categories = array_slice(Request::instance()->route, 2);
	$structure  = $Pages->get_structure();
	$path       = [];
	if (!empty($categories)) {
		foreach ($categories as $category) {
			$category = $Categories->get($category)['path'];
			if (isset($structure['categories'][$category])) {
				$structure = $structure['categories'][$category];
				$path[]    = $structure['path'];
			}
		}
	}
	Page::instance()->title($structure['id'] == 0 ? $L->root_category : $structure['title']);
	$path    = !empty($path) ? implode('/', $path).'/' : '';
	$content = [];
	if (!empty($structure['pages'])) {
		foreach ($structure['pages'] as &$page) {
			$page      = $Pages->get($page);
			$content[] = [
				[
					h::a(
						$page['title'],
						[
							'href' => $path.$page['path']
						]
					),
					[
						'class' => 'cs-static-pages-padding-left-0'
					]
				],
				h::{'a[is=cs-link-button][icon=file-text]'}(
					[
						'href'    => "admin/Static_pages/edit_page/$page[id]",
						'tooltip' => $L->edit
					]
				).
				h::{'a[is=cs-link-button][icon=trash]'}(
					[
						'href'    => "admin/Static_pages/delete_page/$page[id]",
						'tooltip' => $L->delete
					]
				)
			];
		}
		unset($page);
	}
	return $content;
}
