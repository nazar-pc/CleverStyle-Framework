<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'admin/System/Menu',
	function () {
		$L     = Language::instance();
		$Menu  = Menu::instance();
		$route = Route::instance()->path;
		$Menu->add_item(
			'Blogs',
			$L->browse_sections,
			[
				'href'    => 'admin/Blogs/browse_sections',
				'primary' => $route[0] == 'browse_sections'
			]
		);
		$Menu->add_item(
			'Blogs',
			$L->browse_posts,
			[
				'href'    => 'admin/Blogs/browse_posts',
				'primary' => $route[0] == 'browse_posts'
			]
		);
		$Menu->add_item(
			'Blogs',
			$L->general,
			[
				'href'    => 'admin/Blogs',
				'primary' => $route[0] == 'general'
			]
		);
	}
);
