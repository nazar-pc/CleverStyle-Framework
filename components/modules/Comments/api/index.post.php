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
	cs\User;

/**
 * Provides next events:
 *  api/Comments/add
 *  [
 *   'item'   => item    //Item id
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
if (!isset($_POST['item'], $_POST['text'], $_POST['parent'], $_POST['module'])) {
	throw new ExitException(400);
}
$L    = new Prefix('comments_');
$Page = Page::instance();
if (!$_POST['text'] || !strip_tags($_POST['text'])) {
	throw new ExitException($L->comment_cant_be_empty, 400);
}
$allow = false;
Event::instance()->fire(
	'api/Comments/add',
	[
		'item'   => $_POST['item'],
		'module' => $_POST['module'],
		'allow'  => &$allow
	]
);
if (!$allow) {
	throw new ExitException($L->comment_sending_server_error, 500);
}
$Comments = Comments::instance();
$id       = $Comments->add($_POST['item'], $_POST['module'], $_POST['text'], $_POST['parent']);
if ($id) {
	$data             = $Comments->get($id);
	$data['comments'] = false;
	$Page->json($Comments->tree_html([$data]));
} else {
	throw new ExitException($L->comment_sending_server_error, 500);
}
