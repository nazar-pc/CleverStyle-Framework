<?php
/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Comments;
use
	cs\Config,
	cs\Event,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\User;
/**
 * Provides next events:<br>
 *  api/Comments/edit<code>
 *  [
 *   'Comments'		=> <i>&$Comments</i>		//Comments object should be returned in this parameter (after access checking)<br>
 *   'id'			=> <i>id</i>				//Comment id<br>
 *   'module'		=> <i>module</i>			//Module<br>
 *  ]</code>
 */
$Config		= Config::instance();
if (!$Config->module('Comments')->active()) {
	error_code(404);
	return;
}
if (!User::instance()->user()) {
	error_code(403);
	return;
}
$Route = Route::instance();
if (!isset($Route->route[0], $_POST['text'], $_POST['module'])) {
	error_code(400);
	return;
}
$L			= Language::instance();
$Page		= Page::instance();
if (!$_POST['text'] || !strip_tags($_POST['text'])) {
	error_code(400);
	$Page->error($L->comment_cant_be_empty);
	return;
}
$Comments	= false;
Event::instance()->fire(
	'api/Comments/edit',
	[
		'Comments'	=> &$Comments,
		'id'		=> $Route->route[0],
		'module'	=> $_POST['module']
	]
);
if (!is_object($Comments)) {
	error_code(500);
	$Page->error($L->comment_editing_server_error);
	return;
}
/**
 * @var Comments $Comments
 */
$result	= $Comments->set($Route->route[0], $_POST['text']);
if ($result) {
	$Page->json($result['text']);
} else {
	error_code(500);
	$Page->error($L->comment_editing_server_error);
}
