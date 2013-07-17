<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			cs\Config,
			cs\Language,
			cs\Trigger,
			cs\User;
Trigger::instance()->register(
	'admin/System/components/modules/disable',
	function ($data) {
		if ($data['name'] == 'Blogs') {
			clean_pcache();
		}
	}
);
Trigger::instance()->register(
	'admin/System/general/optimization/clean_pcache',
	function () {
		clean_pcache();
	}
);
Trigger::instance()->register(
	'System/Page/rebuild_cache',
	function () {
		if (file_exists(PCACHE.'/module.Blogs.js') && file_exists(PCACHE.'/module.Blogs.css')) {
			return;
		}
		rebuild_pcache();
	}
);
function clean_pcache () {
	if (file_exists(PCACHE.'/module.Blogs.js')) {
		unlink(PCACHE.'/module.Blogs.js');
	}
	if (file_exists(PCACHE.'/module.Blogs.css')) {
		unlink(PCACHE.'/module.Blogs.css');
	}
}
function rebuild_pcache (&$data = null) {
	$key	= [];
	file_put_contents(
		PCACHE.'/module.Blogs.js',
		$key[]	= gzencode(
			file_get_contents(MODULES.'/Blogs/includes/js/general.js'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	file_put_contents(
		PCACHE.'/module.Blogs.css',
		$key[]	= gzencode(
			file_get_contents(MODULES.'/Blogs/includes/css/general.css'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	if ($data !== null) {
		$data['key']	.= md5(implode('', $key));
	}
}
Trigger::instance()->register(
	'System/Index/mainmenu',
	function ($data) {
		if ($data['path'] == 'Blogs') {
			$data['path']	= path(Language::instance()->Blogs);
		}
	}
);
Trigger::instance()->register(
	'api/Comments/add',
	function ($data) {
		$Comments	= null;
		Trigger::instance()->run(
			'Comments/instance',
			[
				'data'	=> &$Comments
			]
		);
		/**
		 * @var \cs\modules\Comments\Comments $Comments
		 */
		if (!(
			$data['module'] == 'Blogs' &&
			Config::instance()->module('Blogs')->enable_comments &&
			User::instance()->user() &&
			$Comments
		)) {
			return true;
		}
		if (Blogs::instance()->get($data['item'])) {
			$Comments->set_module('Blogs');
			$data['Comments']	= $Comments;
		}
		return false;
	}
);
Trigger::instance()->register(
	'api/Comments/edit',
	function ($data) {
		$Comments	= null;
		Trigger::instance()->run(
			'Comments/instance',
			[
				'data'	=> &$Comments
			]
		);
		/**
		 * @var \cs\modules\Comments\Comments $Comments
		 */
		$User		= User::instance();
		if (!(
			$data['module'] == 'Blogs' &&
			Config::instance()->module('Blogs')->enable_comments &&
			$User->user() &&
			$Comments
		)) {
			return true;
		}
		$Comments->set_module('Blogs');
		$comment	= $Comments->get($data['id']);
		if ($comment && ($comment['user'] == $User->id || $User->admin())) {
			$data['Comments']	= $Comments;
		}
		return false;
	}
);
Trigger::instance()->register(
	'api/Comments/delete',
	function ($data) {
		$Comments	= null;
		Trigger::instance()->run(
			'Comments/instance',
			[
				'data'	=> &$Comments
			]
		);
		/**
		 * @var \cs\modules\Comments\Comments $Comments
		 */
		$User		= User::instance();
		if (!(
			$data['module'] == 'Blogs' &&
			Config::instance()->module('Blogs')->enable_comments &&
			$User->user() &&
			$Comments
		)) {
			return true;
		}
		$Comments->set_module('Blogs');
		$comment	= $Comments->get($data['id']);
		if ($comment && ($comment['user'] == $User->id || $User->admin())) {
			$data['Comments']	= $Comments;
			if (
				$comment['parent'] &&
				(
					$comment = $Comments->get($comment['parent'])
				) && (
					$comment['user']  == $User->id || $User->admin()
				)
			) {
				$data['delete_parent']	= true;
			}
		}
		return false;
	}
);