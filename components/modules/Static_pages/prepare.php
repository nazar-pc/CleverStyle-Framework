<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h,
			cs\Config,
			cs\Index,
			cs\Page;
$Config			= Config::instance();
$Static_pages	= Static_pages::instance();
$page			= $Static_pages->get(
	HOME ? $Static_pages->get_structure()['pages']['index'] : $Config->route[0]
);
$Page			= Page::instance();
if ($page['interface']) {
	if (!HOME) {
		Index::instance()->title_auto	= false;
		$Page->title($page['title']);
	}
	$Page->Keywords		= keywords($page['title']);
	$Page->Description	= description($page['content']);
	if (HOME) {
		$Page->canonical_url($Config->base_url());
	} else {
		$category			= $page['category'];
		$canonical_url		= [];
		while ($category) {
			$category			= $Static_pages->get_category($category);
			$canonical_url[]	= $category['path'];
			$category			= $category['parent'];
		}
		unset($category);
		$canonical_url[]	= $page['path'];
		$Page->canonical_url($Config->base_url().'/'.implode('/', $canonical_url));
	}
	$Page->og('type', 'article');
	$Page->content(
		h::section($page['content'])
	);
} else {
	interface_off();
	$Page->Content	= $page['content'];
}