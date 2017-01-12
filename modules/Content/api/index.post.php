<?php
/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Content;
use
	cs\ExitException,
	cs\User;

if (!User::instance()->admin()) {
	throw new ExitException(403);
}

if (!isset($_POST['key'], $_POST['title'], $_POST['content'], $_POST['type'])) {
	throw new ExitException(400);
}

$result = Content::instance()->add($_POST['key'], $_POST['title'], $_POST['content'], $_POST['type']);

if (!$result) {
	throw new ExitException(500);
}
