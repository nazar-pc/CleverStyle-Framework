<?php
/**
 * @package        Static Pages
 * @category       modules
 * @version        0.001
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			\h;
global $Core, $Index, $Page;
$Index->title_auto	= false;
include(MFOLDER.'/../class.php');
$Core->create('cs\\modules\\Static_pages\\Static_pages');
$Page->js('components/modules/'.MODULE.'/includes/js/script.js');
$Page->css('components/modules/'.MODULE.'/includes/css/style.css');
function get_categories_rows ($structure = null, $level = 0) {
	$content	= [];
	if ($structure === null) {
		global $Static_pages, $L;
		$structure	= $Static_pages->get_structure();
		$content[]	= [
			$L->root_category.
			h::{'span.ui-priority-primary.cs-static-pages-count'}(count($structure['pages'])),
			''
		];
	} else {
		$content[]	= [
			[
				$structure['title'].
				h::{'span.ui-priority-primary.cs-static-pages-count'}(count($structure['pages'])),
				[
					'class'	=> 'cs-static-pages-padding-left-'.$level
				]
			],
			''//TODO actions here
		];
	}
	if (count($structure['categories'])) {
		foreach ($structure['categories'] as $category) {
			$content	= array_merge($content, get_categories_rows($category, $level+1));
		}
	}
	return $content;
}
function get_categories_list ($structure = null, $level = 0) {
	$list	= [];
	if ($structure === null) {
		global $Static_pages, $L;
		$structure	= $Static_pages->get_structure();
		$list[0]	= $L->root_category;
		if (count($structure['categories'])) {
			foreach ($structure['categories'] as $category) {
				$list	= array_merge($list, get_categories_list($category, $level+1));
			}
		}
	} else {
		$list[$structure['id']]	= str_repeat('&nbsp;', $level).$structure['title'];
		if (count($structure['categories'])) {
			foreach ($structure['categories'] as $category) {
				$list	= array_merge($list, get_categories_list($category, $level+1));
			}
		}
	}
	return $list;
}