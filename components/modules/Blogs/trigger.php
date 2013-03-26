<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			h;
global $Core;
$Core->register_trigger(
	'admin/System/components/modules/disable',
	function ($data) {
		if ($data['name'] == basename(__DIR__)) {
			clean_pcache();
		}
	}
);
$Core->register_trigger(
	'admin/System/general/optimization/clean_pcache',
	function () {
		clean_pcache();
	}
);
$Core->register_trigger(
	'System/Page/rebuild_cache',
	function () {
		$module	= basename(__DIR__);
		if (file_exists(PCACHE.'/module.'.$module.'.js') && file_exists(PCACHE.'/module.'.$module.'.css')) {
			return;
		}
		rebuild_pcache();
	}
);
function clean_pcache () {
	$module	= basename(__DIR__);
	if (file_exists(PCACHE.'/module.'.$module.'.js')) {
		unlink(PCACHE.'/module.'.$module.'.js');
	}
	if (file_exists(PCACHE.'/module.'.$module.'.css')) {
		unlink(PCACHE.'/module.'.$module.'.css');
	}
}
function rebuild_pcache (&$data = null) {
	$module	= basename(__DIR__);
	$key	= [];
	file_put_contents(
		PCACHE.'/module.'.$module.'.js',
		$key[]	= gzencode(
			file_get_contents(MODULES.'/'.$module.'/includes/js/functions.js').
			file_get_contents(MODULES.'/'.$module.'/includes/js/general.js'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	file_put_contents(
		PCACHE.'/module.'.$module.'.css',
		$key[]	= gzencode(
			file_get_contents(MODULES.'/'.$module.'/includes/css/general.css'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	if ($data !== null) {
		$data['key']	.= md5(implode('', $key));
	}
}
$Core->register_trigger(
	'admin/System/components/modules/install/process',
	function ($data) use ($Core) {
		global $User, $Config;
		$module		= basename(__DIR__);
		if ($data['name'] != $module || !$User->admin()) {
			return;
		}
		$Config->module($module)->set(
			[
				'posts_per_page'	=> 10,
				'max_sections'		=> 3,
				'enable_comments'	=> 1
			]
		);
		return;
	}
);
$Core->register_trigger(
	'admin/System/components/modules/uninstall/process',
	function ($data) use ($Core) {
		global $User, $Cache, $Config, $db, $Blogs;
		$module		= basename(__DIR__);
		if ($data['name'] != $module || !$User->admin()) {
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
		$posts		= $db->{$Config->module($module)->db('posts')}->qfas(
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
			$Cache->$module
		);
		clean_pcache();
		time_limit_pause(false);
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
		global $L, $Config;
		$module	= basename(__DIR__);
		if (!$Config->module($module)->active() && substr($data['rc'], 0, 5) != 'admin') {
			return;
		}
		global $Core;
		require_once __DIR__.'/Blogs.php';
		$Core->create('_cs\\modules\\Blogs\\Blogs');
		$rc		= explode('/', $data['rc']);
		if ($rc[0] == $L->$module) {
			$rc[0]		= $module;
			$data['rc']	= implode('/', $rc);
		}
	}
);