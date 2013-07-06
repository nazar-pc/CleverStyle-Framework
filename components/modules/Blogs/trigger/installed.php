<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
global $Core;
$Core->register_trigger(
	'admin/System/components/modules/uninstall/process',
	function ($data) use ($Core) {
		global $User, $Cache, $Config, $db, $Blogs;
		if ($data['name'] != 'Blogs' || !$User->admin()) {
			return;
		}
		time_limit_pause();
		$sections	= array_keys($Blogs->get_sections_list());
		if (!empty($sections)) {
			foreach ($sections as $section) {
				$Blogs->del_section($section);
			}
			unset($section);
		}
		unset($sections);
		$posts		= $db->{$Config->module('Blogs')->db('posts')}->qfas(
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
			$Cache->Blogs
		);
		clean_pcache();
		time_limit_pause(false);
	}
);
if (!function_exists(__NAMESPACE__.'\\clean_pcache')) {
	function clean_pcache () {
		if (file_exists(PCACHE.'/module.Blogs.js')) {
			unlink(PCACHE.'/module.Blogs.js');
		}
		if (file_exists(PCACHE.'/module.Blogs.css')) {
			unlink(PCACHE.'/module.Blogs.css');
		}
	}
}