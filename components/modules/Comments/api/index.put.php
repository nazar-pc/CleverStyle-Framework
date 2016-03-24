<?php
/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Comments;
use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language\Prefix,
	cs\Page,
	cs\Request,
	cs\User;

/**
 * Provides next events:
 *  api/Comments/edit
 *  [
 *   'Comments' => <i>&$Comments //Comments object should be returned in this parameter (after access checking)<br>
 *   'id'       => <i>id         //Comment id<br>
 *   'module'   => <i>module     //Module<br>
 *  ]
 */
$Config = Config::instance();
if (!$Config->module('Comments')->enabled()) {
	throw new ExitException(404);
}
if (!User::instance()->user()) {
	throw new ExitException(403);
}
$Request = Request::instance();
if (!isset($Request->route[0], $_POST['text'], $_POST['module'])) {
	throw new ExitException(400);
}
$L    = new Prefix('comments_');
$Page = Page::instance();
if (!$_POST['text'] || !strip_tags($_POST['text'])) {
	throw new ExitException($L->comment_cant_be_empty, 400);
}
$Comments = false;
Event::instance()->fire(
	'api/Comments/edit',
	[
		'Comments' => &$Comments,
		'id'       => $Request->route[0],
		'module'   => $_POST['module']
	]
);
if (!is_object($Comments)) {
	throw new ExitException($L->comment_editing_server_error, 500);
}
/**
 * @var Comments $Comments
 */
$result = $Comments->set($Request->route[0], $_POST['text']);
if ($result) {
	$Page->json($result['text']);
} else {
	throw new ExitException($L->comment_editing_server_error, 500);
}
