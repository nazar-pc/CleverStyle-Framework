<?php
/**
 * @package        Blogs
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
Trigger::instance()->register(
	'admin/System/Menu',
	function () {
		$L		= Language::instance();
		$Menu	= Menu::instance();
		$Menu->add_item('Blogs', $L->browse_sections, 'admin/Blogs/browse_sections');
		$Menu->add_item('Blogs', $L->browse_posts, 'admin/Blogs/browse_posts');
		$Menu->add_item('Blogs', $L->general, 'admin/Blogs');
	}
);
