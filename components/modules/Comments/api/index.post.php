<?php
/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Comments;
use			cs\Config,
			cs\Language,
			cs\Page,
			cs\Trigger,
			cs\User;
/**
 * Provides next triggers:<br>
 *  api/Comments/add<code>
 *  [
 *   'Comments'	=> <i>&$Comments</i>	//Comments object should be returned in this parameter (after access checking)<br>
 *   'item'		=> <i>item</i>			//Item id<br>
 *   'module'	=> <i>module</i>		//Module<br>
 *  ]</code>
 */
$Config		= Config::instance();
if (!$Config->module('Comments')->active()) {
	define('ERROR_CODE', 404);
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
	define('ERROR_CODE', 403);
	return;
}
if (!isset($_POST['item'], $_POST['text'], $_POST['parent'], $_POST['module'])) {
	define('ERROR_CODE', 400);
	return;
}
$L			= Language::instance();
$Page		= Page::instance();
if (!$_POST['text'] || !strip_tags($_POST['text'])) {
	define('ERROR_CODE', 400);
	$Page->error($L->comment_cant_be_empty);
	return;
}
$Comments	= false;
Trigger::instance()->run(
	'api/Comments/add',
	[
		'Comments'	=> &$Comments,
		'item'		=> $_POST['item'],
		'module'	=> $_POST['module']
	]
);
if (!is_object($Comments)) {
	if (!defined('ERROR_CODE')) {
		define('ERROR_CODE', 500);
		$Page->error($L->comment_sending_server_error);
	}
	return;
}
/**
 * @var Comments $Comments
 */
$result		= $Comments->add($_POST['item'], $_POST['text'], $_POST['parent']);
if ($result) {
	$result['comments']	= false;
	$Page->json($Comments->tree_html([$result]));
} else {
	define('ERROR_CODE', 500);
	$Page->error($L->comment_sending_server_error);
}