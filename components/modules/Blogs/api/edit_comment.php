<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
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
if (!$_POST['text'] || !strip_tags($_POST['text'])) {
	define('ERROR_CODE', 400);
	$Page->error($L->comment_cant_be_empty);
	return;
}
if (
	!($Blogs->get($_POST['id'])['user'] == $User->id) &&
	!(
		$User->admin() &&
		$User->get_user_permission('admin/'.MODULE, 'index') &&
		$User->get_user_permission('admin/'.MODULE, 'edit_comment')
	)
) {
	define('ERROR_CODE', 403);
	$Page->error($L->access_denied);
	return;
}
$result	= $Blogs->set_comment($_POST['id'], $_POST['text']);
if ($result) {
	$Page->json($result['text']);
} else {
	define('ERROR_CODE', 500);
	$Page->error($L->comment_editing_server_error);
}