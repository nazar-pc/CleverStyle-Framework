<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			cs\Cache,
			cs\Config,
			cs\DB,
			cs\Trigger,
			cs\User;
Trigger::instance()->register(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		if ($data['name'] != 'Blogs' || !User::instance()->admin()) {
			return;
		}
		time_limit_pause();
		$Blogs		= Blogs::instance();
		$sections	= array_keys($Blogs->get_sections_list());
		if (!empty($sections)) {
			foreach ($sections as $section) {
				$Blogs->del_section($section);
			}
			unset($section);
		}
		unset($sections);
		$posts		= DB::instance()->{Config::instance()->module('Blogs')->db('posts')}->qfas(
			"SELECT `id`
			FROM `[prefix]blogs_posts`"
		);
		if (!empty($posts)) {
			foreach ($posts as $post) {
				$Blogs->del($post);
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
