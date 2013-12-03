<?php
/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Comments;
use			h,
			cs\Config,
			cs\Language,
			cs\Page,
			cs\Trigger,
			cs\User;
/**
 * Provides next triggers:<br>
 *  api/Comments/delete<code>
 *  [
 *   'Comments'			=> <i>&$Comments</i>		//Comments object should be returned in this parameter (after access checking)<br>
 *   'delete_parent'	=> <i>&$delete_parent</i>	//Boolean parameter, should contain boolean true, if parent comment may be deleted by current user<br>
 *   'id'				=> <i>id</i>				//Comment id<br>
 *   'module'			=> <i>module</i>			//Module<br>
 *  ]</code>
 */
$Config			= Config::instance();
if (!$Config->module('Comments')->active()) {
	error_code(404);
	return;
}
/**
 * If AJAX request from local referer, user is not guest - allow
 */
if (!(
	$Config->server['referer']['local'] &&
	$Config->server['ajax'] &&
	User::instance()->user()
)) {
	sleep(1);
	error_code(403);
	return;
}
if (!isset($Config->route[0], $_POST['module'])) {
	error_code(400);
	return;
}
$Comments		= false;
$delete_parent	= false;
Trigger::instance()->run(
	'api/Comments/delete',
	[
		'Comments'		=> &$Comments,
		'delete_parent'	=> &$delete_parent,
		'id'			=> $Config->route[0],
		'module'		=> $_POST['module']
	]
);
$L				= Language::instance();
$Page			= Page::instance();
if (!is_object($Comments)) {
	if (!defined('ERROR_CODE')) {
		error_code(500);
		$Page->error($L->comment_deleting_server_error);
	}
	return;
}
/**
 * @var Comments $Comments
 */
if ($result = $Comments->del($Config->route[0])) {
	$Page->json($delete_parent ? h::{'icon.cs-comments-comment-delete.cs-pointer'}('trash') : '');
} else {
	error_code(500);
	$Page->error($L->comment_deleting_server_error);
}