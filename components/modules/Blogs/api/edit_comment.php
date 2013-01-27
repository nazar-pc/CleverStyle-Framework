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
	return;
}
if (!$_POST['text'] || !strip_tags($_POST['text'])) {
	$Page->content(
		_json_encode([
			'status'	=> $L->comment_cant_be_empty
		])
	);
}
if (
	!($Blogs->get($_POST['id'])['user'] == $User->id) &&
	!(
		$User->admin() &&
		$User->get_user_permission('admin/'.MODULE, 'index') &&
		$User->get_user_permission('admin/'.MODULE, 'edit_comment')
	)
) {
	$Page->content(
		_json_encode([
			'status'	=> $L->access_denied
		])
	);
}
$result	= $Blogs->set_comment($_POST['id'], $_POST['text']);
$Page->content(
	_json_encode($result ? [
		'status'	=> 'OK',
		'content'	=> $result['text']
	] : [
		'status'	=> $L->comment_editing_server_error
	])
);