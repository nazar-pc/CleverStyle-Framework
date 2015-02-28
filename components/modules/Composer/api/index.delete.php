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
	cs\Page,
	cs\User;
if (!isset($_POST['name'], $_POST['type'])) {
	error_code(400);
	return;
}
if (!User::instance()->admin()) {
	error_code(403);
	return;
}
require_once __DIR__.'/../ansispan.php';
$result = Composer::instance()->update($_POST['name'], $_POST['type'], Composer::MODE_DELETE);
Page::instance()->json([
	'code'        => $result['code'],
	'description' => ansispan($result['description'])
]);
