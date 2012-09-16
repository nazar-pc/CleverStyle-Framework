<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
global $Config, $User, $Page, $Blogs, $L;
include_once MFOLDER.'/../prepare.php';
/**
 * If AJAX request from local referer, user is not guest
 */
if (!$Config->server['referer']['local'] || !$Config->server['ajax'] || !$User->is('user')) {
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
$result					= $Blogs->add_comment($_POST['post'], $_POST['text'], $_POST['parent']);
$ressult['comments']	= false;
$Page->content(
	_json_encode($result ? [
		'status'	=> 'OK',
		'content'	=> get_comments_tree([$result], $Blogs->get($result['post']))
	] : [
		'status'	=> $L->comment_sending_server_error
	])
);