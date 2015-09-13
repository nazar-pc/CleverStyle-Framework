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
	cs\ExitException,
	cs\Page;

_header('Cache-Control: no-store');
_header('Pragma: no-cache');
$OAuth2 = OAuth2::instance();
if (!isset($_POST['access_token'])) {
	$e = new ExitException(
		[
			'invalid_request',
			'access_token parameter required'
		],
		400
	);
	$e->getJson();
	throw $e;
}
if (!$OAuth2->del_token($_POST['access_token'])) {
	$e = new ExitException(
		[
			'server_error',
			"Server can't invalidate token, try later"
		],
		500
	);
	$e->getJson();
	throw $e;
}
interface_off();
