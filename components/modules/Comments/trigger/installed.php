<?php
/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Comments;
use			cs\Cache,
			cs\User,
			cs\Trigger;
Trigger::instance()->register(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		if ($data['name'] != 'Comments' || !User::instance()->admin()) {
			return;
		}
		time_limit_pause();
		unset(Cache::instance()->Comments);
		clean_pcache();
		time_limit_pause(false);
	}
);
if (!function_exists(__NAMESPACE__.'\\clean_pcache')) {
	function clean_pcache () {
		if (file_exists(PCACHE.'/module.Comments.js')) {
			unlink(PCACHE.'/module.Comments.js');
		}
		if (file_exists(PCACHE.'/module.Comments.css')) {
			unlink(PCACHE.'/module.Comments.css');
		}
	}
}