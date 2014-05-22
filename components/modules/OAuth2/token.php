<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\OAuth2;
use
	cs\Config,
	cs\Index,
	cs\Page,
	cs\User;
header('Cache-Control: no-store');
header('Pragma: no-cache');
interface_off();
/**
 * Errors processing
 */
$Index	= Index::instance();
$Page	= Page::instance();
if (!isset($_GET['grant_type'])) {
	error_code(400);
	$Page->error([
		'invalid_request',
		'grant_type parameter required'
	], true);
}
if (!isset($_GET['client_id'])) {
	error_code(400);
	$Page->error([
		'invalid_request',
		'client_id parameter required'
	], true);
}
if (!isset($_GET['client_secret'])) {
	error_code(400);
	$Page->error([
		'invalid_request',
		'client_secret parameter required'
	], true);
}
$OAuth2	= OAuth2::instance();
if (!($client = $OAuth2->get_client($_GET['client_id']))) {
	error_code(400);
	$Page->error([
		'access_denied',
		'Invalid client id'
	], true);
} elseif (!$client['active']) {
	error_code(403);
	$Page->error([
		'access_denied',
		'Inactive client id'
	], true);
}
if ($_GET['client_secret'] != $client['secret']) {
	error_code(400);
	$Page->error([
		'access_denied',
		'client_secret do not corresponds client_id'
	], true);
}
if (!$client['active']) {
	error_code(400);
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
if ($_GET['grant_type'] != 'guest_token' && !isset($_GET['redirect_uri'])) {
	error_code(400);
	$Page->error([
		'invalid_request',
		'redirect_uri parameter required'
	], true);
} elseif ($_GET['grant_type'] != 'guest_token' && !preg_match("/^[^\/]+:\/\/$client[domain]/", urldecode($_GET['redirect_uri']))) {
	error_code(400);
	$Page->error([
		'invalid_request',
		'Invalid redirect_uri parameter'
	], true);
}
if (!in_array($_GET['grant_type'], ['authorization_code', 'refresh_token', 'guest_token'])) {
	error_code(400);
	$Page->error([
		'unsupported_grant_type',
		'Specified grant type is not supported, only "authorization_code" or "refresh_token" types available'
	], true);
}
/**
 * Tokens operations processing
 */
switch ($_GET['grant_type']) {
	case 'authorization_code':
		if (!isset($_GET['code'])) {
			error_code(400);
			$Page->error([
				'invalid_request',
				'code parameter required'
			], true);
		}
		$token_data	= $OAuth2->get_code($_GET['code'], $client['id'], $client['secret'], urldecode($_GET['redirect_uri']));
		if (!$token_data) {
			error_code(403);
			$Page->error([
				'server_error',
				"Server can't get token data, try later"
			], true);
		}
		if ($token_data['expires_in'] < 0) {
			error_code(403);
			$Page->error([
				'access_denied',
				'access_token expired'
			], true);
		}
		$Page->json($token_data);
		$Index->stop	= true;
		return;
	case 'refresh_token':
		if (!isset($_GET['refresh_token'])) {
			error_code(400);
			$Page->error([
				'refresh_token',
				'refresh_token parameter required'
			], true);
		}
		$token_data	= $OAuth2->refresh_token($_GET['refresh_token'], $client['id'], $client['secret']);
		if (!$token_data) {
			error_code(403);
			$Page->error([
				'access_denied',
				'User session invalid'
			], true);
		}
		$Page->json($token_data);
		$Index->stop	= true;
		return;
	case 'guest_token':
		if (User::instance()->user()) {
			error_code(403);
			$Page->error([
				'access_denied',
				'Only guests, not user allowed to access this grant_type'
			], true);
		}
		if (!Config::instance()->module('OAuth2')->guest_tokens) {
			error_code(403);
			$Page->error([
				'access_denied',
				'Guest tokens disabled'
			], true);
		}
		$code	= $OAuth2->add_code($client['id'], 'code', '');
		if (!$code) {
			error_code(500);
			$Page->error([
				'server_error',
				"Server can't generate code, try later"
			], true);
		}
		$token_data	= $OAuth2->get_code($code, $client['id'], $client['secret'], '');
		if ($token_data) {
			$Page->json($token_data);
			$Index->stop	= true;
			return;
		} else {
			error_code(500);
			$Page->error([
				'server_error',
				"Server can't get token data, try later"
			], true);
		}
}
