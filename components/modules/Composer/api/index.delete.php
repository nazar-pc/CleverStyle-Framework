<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\ExitException,
	cs\Page,
	cs\User;
if (!isset($_POST['name'], $_POST['category'])) {
	throw new ExitException(400);
}
if (!User::instance()->admin()) {
	throw new ExitException(403);
}
require_once __DIR__.'/../ansispan.php';
$result = Composer::instance()->update($_POST['name'], $_POST['category'], Composer::MODE_DELETE);
Page::instance()->json([
	'code'        => $result['code'],
	'description' => ansispan($result['description'])
]);
