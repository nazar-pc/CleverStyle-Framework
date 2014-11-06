<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use
	h,
	cs\Config,
	cs\Page\Meta,
	cs\Page;
$Config			= Config::instance();
$Static_pages	= Static_pages::instance();
$page			= $Static_pages->get(
	home_page() ? $Static_pages->get_structure()['pages']['index'] : $Config->route[0]
);
$Page			= Page::instance();
if ($page['interface']) {
	if (!home_page()) {
		$Page->Title[1]	= $page['title'];
	}
	$Page->Description	= description($page['content']);
	if (home_page()) {
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
	Meta::instance()->article();
	$Page->content(
		h::section($page['content'])
	);
} else {
	interface_off();
	$Page->Content	= $page['content'];
}
