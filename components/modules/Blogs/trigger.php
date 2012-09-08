<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Core, $Config;
$clean_pcache = function ($data = null) {
	$module	= basename(__DIR__);
	if ($data['name'] == $module || $data === null) {
		if (file_exists(PCACHE.'/module.'.$module.'.js')) {
			unlink(PCACHE.'/module.'.$module.'.js');
		}
		if (file_exists(PCACHE.'/module.'.$module.'.css')) {
			unlink(PCACHE.'/module.'.$module.'.css');
		}
	}
};
$Core->register_trigger(
	'admin/System/components/modules/disable',
	$clean_pcache
);
$Core->register_trigger(
	'admin/System/general/optimization/clean_pcache',
	$clean_pcache
);
$Core->register_trigger(
	'System/Page/rebuild_cache',
	function ($data) {
		$module	= basename(__DIR__);
		if (file_exists(PCACHE.'/module.'.$module.'.js') && file_exists(PCACHE.'/module.'.$module.'.css')) {
			return;
		}
		file_put_contents(
			PCACHE.'/module.'.$module.'.js',
			$key	= gzencode(
				file_get_contents(MODULES.'/'.$module.'/includes/js/functions.js').
				file_get_contents(MODULES.'/'.$module.'/includes/js/general.js'),
				9
			),
			LOCK_EX | FILE_BINARY
		);
		$data['key']	.= md5($key);
		file_put_contents(
			PCACHE.'/module.'.$module.'.css',
			$key	= gzencode(
				file_get_contents(MODULES.'/'.$module.'/includes/css/general.css'),
				9
			),
			LOCK_EX | FILE_BINARY
		);
		$data['key']	.= md5($key);
	}
);
$Core->register_trigger(
	'admin/System/components/modules/install/process',
	function ($data) use ($Core) {
		global $User, $Config;
		$module		= basename(__DIR__);
		if ($data['name'] != $module || !$User->is('admin')) {
			return true;
		}
		time_limit_pause();
		$Config->module($module)->set(
			[
				'posts_per_page'	=> 10,
				'max_sections'		=> 3,
				'enable_comments'	=> 1
			]
		);
		time_limit_pause(false);
		return true;
	}
);
$Core->register_trigger(
	'admin/System/components/modules/uninstall/process',
	function ($data) use ($Core) {
		global $User, $Cache, $Config, $db;
		$module		= basename(__DIR__);
		if ($data['name'] != $module || !$User->is('admin')) {
			return true;
		}
		time_limit_pause();
		include_once MODULES.'/'.$module.'/class.php';
		$Blogs		= $Core->create('cs\\modules\\Blogs\\Blogs');
		$sections	= array_keys($Blogs->get_sections_list());
		if (!empty($sections)) {
			foreach ($sections as $section) {
				$Blogs->del_section($section);
			}
			unset($section);
		}
		unset($sections);
		$posts		= $db->{$Config->module($module)->db('posts')}->qfa(
			"SELECT `id` FROM `[prefix]blogs_posts`",
			true
		);
		if (!empty($posts)) {
			foreach ($posts as $post) {
				$Blogs->del($post);
			}
			unset($post);
		}
		unset($posts);
		unset($Cache->$module);
		time_limit_pause(false);
		return true;
	}
);
$Core->register_trigger(
	'System/Index/mainmenu',
	function ($data) {
		global $L;
		$module	= basename(__DIR__);
		if ($data['module'] == $module) {
			$data['module']	= path($L->$module);
		}
	}
);
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($data) {
		global $L;
		$module	= basename(__DIR__);
		$rc		= explode('/', $data['rc']);
		if ($rc[0] == $L->$module) {
			$rc[0]		= $module;
			$data['rc']	= implode('/', $rc);
		}
	}
);