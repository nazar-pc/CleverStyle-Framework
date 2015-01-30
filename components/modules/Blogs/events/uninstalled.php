<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Event::instance()->on(
	'admin/System/components/modules/install/process',
	function ($data) {
		if ($data['name'] != 'Blogs') {
			return;
		}
		Config::instance()->module('Blogs')->set([
			'posts_per_page'				=> 10,
			'max_sections'					=> 3,
			'enable_comments'				=> 1,
			'new_posts_only_from_admins'	=> 1,
			'allow_iframes_without_content'	=> 1
		]);
		return;
	}
);
