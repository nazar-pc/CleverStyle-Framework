<?php
/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Comments;
use
	h,
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\User;
/**
 * Provides next events:
 *  api/Comments/delete
 *  [
 *   'Comments'      => &$Comments      //Comments object should be returned in this parameter (after access checking)<br>
 *   'delete_parent' => &$delete_parent //Boolean parameter, should contain boolean true, if parent comment may be deleted by current user<br>
 *   'id'            => id              //Comment id<br>
 *   'module'        => module          //Module<br>
 *  ]
 */
$Config = Config::instance();
if (!$Config->module('Comments')->active()) {
	throw new ExitException(404);
}
if (!User::instance()->user()) {
	throw new ExitException(403);
}
$Route = Route::instance();
if (!isset($Route->route[0], $_POST['module'])) {
	throw new ExitException(400);
}
$Comments      = false;
$delete_parent = false;
Event::instance()->fire(
	'api/Comments/delete',
	[
		'Comments'      => &$Comments,
		'delete_parent' => &$delete_parent,
		'id'            => $Route->route[0],
		'module'        => $_POST['module']
	]
);
$L    = Language::instance();
$Page = Page::instance();
if (!is_object($Comments)) {
	throw new ExitException($L->comment_deleting_server_error, 500);
}
/**
 * @var Comments $Comments
 */
if ($result = $Comments->del($Route->route[0])) {
	$Page->json($delete_parent ? h::{'icon.cs-comments-comment-delete.cs-cursor-pointer'}('trash') : '');
} else {
	throw new ExitException($L->comment_deleting_server_error, 500);
}
