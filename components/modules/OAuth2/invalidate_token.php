<?php
/**
 * @package        OAuth2
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\OAuth2;

use
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;

header('Cache-Control: no-store');
header('Pragma: no-cache');
/**
 * Errors processing
 */
$Config = Config::instance();
$Index  = Index::instance();
$L      = new Prefix('oauth2_');
$Page   = Page::instance();
if (!isset($_GET['client_id'])) {
	error_code(400);
	$Page->error([
		'invalid_request',
		'client_id parameter required'
	], true);
}
if (!isset($_GET['access_token'])) {
	error_code(400);
	$Page->error([
		'invalid_request',
		'access_token parameter required'
	], true);
}
$OAuth2 = OAuth2::instance();
$client = $OAuth2->get_client($_GET['client_id']);
if (!$client) {
	error_code(400);
	$Page->error([
		'access_denied',
		'Invalid client id'
	], true);
}
if (!$client['active']) {
	error_code(403);
	$Page->error([
		'access_denied',
		'Inactive client id'
	], true);
}
if (!$client['domain']) {
	error_code(400);
	$Page->error([
		'unauthorized_client',
		'Request method is not authored'
	], true);
}
$token_data = $OAuth2->get_token($_GET['access_token'], $client['id'], $client['secret']);
if ($token_data['type'] == 'code') { // Server request with client_secret needed
	if (!isset($_GET['client_secret']) || $_GET['client_secret'] != $client['secret']) {
		error_code(400);
		$Page->error([
			'access_denied',
			'client_secret do not corresponds client_id'
		], true);
	}
	if (!$OAuth2->del_token($_GET['access_token'], $client['id'])) {
		error_code(500);
		$Page->error([
			'server_error',
			"Server can't invalidate token, try later"
		], true);
	}
	interface_off();
} else { // Client request with redirect_uri needed
	if (!isset($_GET['redirect_uri'])) {
		code_header(400);
		$Page->Content = '';
		$Page->warning($L->redirect_uri_parameter_required);
		$Index->stop = true;
		return;
	}
	if (
		urldecode($_GET['redirect_uri']) != $Config->base_url().'/OAuth2/blank/' &&
		!preg_match("/^[^\/]+:\/\/$client[domain]/", urldecode($_GET['redirect_uri']))
	) {
		code_header(400);
		$Page->Content = '';
		$Page->warning($L->redirect_uri_parameter_invalid);
		$Index->stop = true;
		return;
	}
	if (!$OAuth2->del_token($_GET['access_token'], $client['id'])) {
		header(
			'Location: '.uri_for_token(
				http_build_url(
					urldecode($_GET['redirect_uri']),
					[
						'error'             => 'server_error',
						'error_description' => "Server can't invalidate token, try later",
						'state'             => isset($_GET['state']) ? $_GET['state'] : false
					]
				)
			),
			true,
			302
		);
	} else {
		header(
			'Location: '.uri_for_token(
				http_build_url(
					urldecode($_GET['redirect_uri']),
					[
						'state' => isset($_GET['state']) ? $_GET['state'] : false
					]
				)
			),
			true,
			302
		);
	}
	$Page->Content = '';
	$Index->stop   = true;
	return;
}
