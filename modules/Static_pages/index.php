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
	cs\Config,
	cs\ExitException,
	cs\Page\Meta,
	cs\Page,
	cs\Request,
	cs\User;

$Config     = Config::instance();
$Page       = Page::instance();
$Request    = Request::instance();
$Categories = Categories::instance();
$page       = Pages::instance()->get($Request->route(0));
array_pop($Page->Title);
if (!$page) {
	throw new ExitException(404);
}
$User = User::instance();
if ($page['interface']) {
	if (!$Request->home_page) {
		$Page->title($page['title']);
	}
	$Page->Description = description($page['content']);
	$Meta              = Meta::instance();
	$Meta->article();
	if (preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"]/i', $page['content'], $images)) {
		$Meta->image($images[1]);
	}
	unset($images);
	if ($Request->home_page) {
		$canonical_url = $Config->base_url();
		$Page->canonical_url($canonical_url);
	} else {
		$category      = $page['category'];
		$canonical_url = [];
		while ($category) {
			$category        = $Categories->get($category);
			$canonical_url[] = $category['path'];
			$category        = $category['parent'];
		}
		unset($category);
		$canonical_url[] = $page['path'];
		$canonical_url   = $Config->base_url().'/'.implode('/', $canonical_url);
		$Page->canonical_url($canonical_url);
	}
	$Page->content(
		h::cs_static_pages_page(
			h::section($page['content']),
			[
				'id'    => $page['id'],
				'admin' => $User->admin()
			]
		)
	);
} else {
	$Page->interface = false;
	$Page->Content   = $page['content'];
}
