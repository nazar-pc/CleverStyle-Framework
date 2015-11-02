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
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language,
	cs\Page,
	cs\User;
/**
 * Provides next events:
 *  api/Comments/add
 *  [
 *   'Comments' => &$Comments //Comments object should be returned in this parameter (after access checking)<br>
 *   'item'     => item       //Item id<br>
 *   'module'   => module     //Module<br>
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
$L    = Language::instance();
$Page = Page::instance();
if (!$_POST['text'] || !strip_tags($_POST['text'])) {
	throw new ExitException($L->comment_cant_be_empty, 400);
}
$Comments = false;
Event::instance()->fire(
	'api/Comments/add',
	[
		'Comments' => &$Comments,
		'item'     => $_POST['item'],
		'module'   => $_POST['module']
	]
);
if (!is_object($Comments)) {
	throw new ExitException($L->comment_sending_server_error, 500);
}
/**
 * @var Comments $Comments
 */
$result = $Comments->add($_POST['item'], $_POST['text'], $_POST['parent']);
if ($result) {
	$result['comments'] = false;
	$Page->json($Comments->tree_html([$result]));
} else {
	throw new ExitException($L->comment_sending_server_error, 500);
}
