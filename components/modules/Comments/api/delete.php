<?php
/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Comments;
use			h;
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
global $Config, $User, $Page, $L, $Core;
if (!$Config->module('Comments')->active()) {
	define('ERROR_CODE', 404);
	return;
}
/**
 * If AJAX request from local referer, user is not guest
 */
if (!$Config->server['referer']['local'] || !$Config->server['ajax'] || !$User->user()) {
	sleep(1);
	define('ERROR_CODE', 403);
	return;
}
if (!isset($_POST['id'], $_POST['module'])) {
	define('ERROR_CODE', 400);
	return;
}
$Comments		= false;
$delete_parent	= false;
$Core->run_trigger(
	'api/Comments/delete',
	[
		'Comments'		=> &$Comments,
		'delete_parent'	=> &$delete_parent,
		'id'			=> $_POST['id'],
		'module'		=> $_POST['module']
	]
);
if (!is_object($Comments)) {
	if (!defined('ERROR_CODE')) {
		define('ERROR_CODE', 500);
		$Page->error($L->comment_deleting_server_error);
	}
	return;
}
/**
 * @var Comments $Comments
 */
if ($result = $Comments->del($_POST['id'])) {
	$Page->json($delete_parent ? h::{'icon.cs-comments-comment-delete.cs-pointer'}('trash') : '');
} else {
	define('ERROR_CODE', 500);
	$Page->error($L->comment_deleting_server_error);
}