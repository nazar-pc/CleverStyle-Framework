<?php
/**
 * @package        OAuth2
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\OAuth2;

use
	cs\Page;

header('Cache-Control: no-store');
header('Pragma: no-cache');
$Page   = Page::instance();
$OAuth2 = OAuth2::instance();
if (!isset($_POST['access_token'])) {
	error_code(400);
	$Page->error([
		'invalid_request',
		'access_token parameter required'
	], true);
}
if (!$OAuth2->del_token($_POST['access_token'])) {
	error_code(500);
	$Page->error([
		'server_error',
		"Server can't invalidate token, try later"
	], true);
}
interface_off();
