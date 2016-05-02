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
 *  api/Comments/delete
 *  [
 *   'id'     => id      //Comment id
 *   'user'   => user    //User id
 *   'item'   => item_id //Item id
 *   'module' => module  //Module
 *   'allow'  => &$allow //Whether allow or not
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
if (!isset($Request->route[0], $_POST['module'])) {
	throw new ExitException(400);
}
$Comments = Comments::instance();
$comment  = $Comments->get($Request->route[0]);
if (!$comment) {
	throw new ExitException(404);
}
$allow = false;
Event::instance()->fire(
	'api/Comments/delete',
	[
		'Comments' => &$Comments,
		'id'       => $Request->route[0],
		'module'   => $_POST['module'],
		'allow'    => &$allow
	]
);
$L    = new Prefix('comments_');
$Page = Page::instance();
if (!$allow) {
	throw new ExitException($L->comment_deleting_server_error, 500);
}
if ($Comments->del($Request->route[0])) {
	$Page->json('');//TODO: get rid of this
} else {
	throw new ExitException($L->comment_deleting_server_error, 500);
}
