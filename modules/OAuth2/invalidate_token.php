<?php
/**
 * @package  OAuth2
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\OAuth2;
use
	cs\ExitException,
	cs\Page,
	cs\Response;

Response::instance()
	->header('cache-control', 'no-store')
	->header('pragma', 'no-cache');
$OAuth2 = OAuth2::instance();
if (!isset($_POST['access_token'])) {
	$e = new ExitException(
		[
			'invalid_request',
			'access_token parameter required'
		],
		400
	);
	$e->setJson();
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
	$e->setJson();
	throw $e;
}
Page::instance()->interface = false;
