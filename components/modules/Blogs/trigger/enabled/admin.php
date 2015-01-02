<?php
/**
 * @package        Blogs
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
Trigger::instance()->register(
	'admin/System/Menu',
	function () {
		$L		= Language::instance();
		$Menu	= Menu::instance();
		$route	= Index::instance()->route_path;
		$Menu->add_item(
			'Blogs',
			$L->browse_sections,
			'admin/Blogs/browse_sections',
			[
				'class'	=> $route[0] == 'browse_sections' ? 'uk-active' : false
			]
		);
		$Menu->add_item(
			'Blogs',
			$L->browse_posts,
			'admin/Blogs/browse_posts',
			[
				'class'	=> $route[0] == 'browse_posts' ? 'uk-active' : false
			]
		);
		$Menu->add_item(
			'Blogs',
			$L->general,
			'admin/Blogs',
			[
				'class'	=> $route[0] == 'general' ? 'uk-active' : false
			]
		);
	}
);
