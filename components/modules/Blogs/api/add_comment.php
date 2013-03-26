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
$result				= $Blogs->add_comment($_POST['post'], $_POST['text'], $_POST['parent']);
if ($result) {
	$result['comments']	= false;
	$Page->json(get_comments_tree([$result], $Blogs->get($result['post'])));
} else {
	define('ERROR_CODE', 500);
	$Page->error($L->comment_sending_server_error);
}