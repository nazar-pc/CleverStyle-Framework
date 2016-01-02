<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	cs\Cache,
	cs\Config,
	cs\DB,
	cs\Event;
Event::instance()->on(
	'admin/System/components/modules/uninstall/before',
	function ($data) {
		if ($data['name'] != 'Blogs') {
			return;
		}
		time_limit_pause();
		$Posts    = Posts::instance();
		$Sections = Sections::instance();
		$sections = array_keys($Sections->get_list());
		if (!empty($sections)) {
			foreach ($sections as $section) {
				$Sections->del($section);
			}
			unset($section);
		}
		unset($sections);
		$posts = DB::instance()->{Config::instance()->module('Blogs')->db('posts')}->qfas(
			"SELECT `id`
			FROM `[prefix]blogs_posts`"
		);
		if (!empty($posts)) {
			foreach ($posts as $post) {
				$Posts->del($post);
			}
			unset($post);
		}
		unset(
			$posts,
			Cache::instance()->Blogs
		);
		time_limit_pause(false);
	}
);
