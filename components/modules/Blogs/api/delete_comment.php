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
global $Config, $User, $Page, $Blogs, $L;
include_once MFOLDER.'/../prepare.php';
/**
 * If AJAX request from local referer, user is not guest
 */
if (!$Config->server['referer']['local'] || !$Config->server['ajax'] || !$User->user()) {
	sleep(1);
	define('ERROR_CODE', 403);
	return;
}
if (
	!($Blogs->get($_POST['id'])['user'] == $User->id) &&
	!(
		$User->admin() &&
		$User->get_user_permission('admin/'.MODULE, 'index') &&
		$User->get_user_permission('admin/'.MODULE, 'delete_comment')
	)
) {
	define('ERROR_CODE', 403);
	$Page->error($L->access_denied);
	return;
}
$comment	= $Blogs->get_comment($_POST['id']);
if ($comment && ($result = $Blogs->del_comment($_POST['id']))) {
	$Page->json(
		!$comment['comments'] && (
			$User->id == $comment['user'] ||
			(
				$User->admin() &&
				$User->get_user_permission('admin/'.MODULE, 'index') &&
				$User->get_user_permission('admin/'.MODULE, 'delete_comment')
			)
		) ? h::{'icon.cs-blogs-comment-delete.cs-pointer'}('trash') : ''
	);
} else {
	define('ERROR_CODE', 500);
	$Page->error($L->comment_deleting_server_error);
}