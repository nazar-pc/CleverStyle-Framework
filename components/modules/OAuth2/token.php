<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\OAuth2;
use			h;
global $Page, $OAuth2, $Index;
header('Content-Type: application/json', true);
header('Cache-Control: no-store');
header('Pragma: no-cache');
interface_off();
/**
 * Errors processing
 */
if (!isset($_GET['grant_type'])) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'invalid_request',
		'error_description'	=> 'grant_type parameter required'
	]);
	$Index->stop	= true;
	return;
}
if (!isset($_GET['client_id'])) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'invalid_request',
		'error_description'	=> 'client_id parameter required'
	]);
	$Index->stop	= true;
	return;
}
if (!isset($_GET['client_secret'])) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'invalid_request',
		'error_description'	=> 'client_secret parameter required'
	]);
	$Index->stop	= true;
	return;
}
if (!($client = $OAuth2->get_client($_GET['client_id']))) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'access_denied',
		'error_description'	=> 'Invalid client id'
	]);
	$Index->stop	= true;
	return;
} elseif (!$client['active']) {
	code_header(403);
	$Page->Content	= _json_encode([
		'error'				=> 'access_denied',
		'error_description'	=> 'Inactive client id'
	]);
	$Index->stop	= true;
	return;
}
if ($_GET['client_secret'] != $client['secret']) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'access_denied',
		'error_description'	=> 'client_secret do not corresponds client_id'
	]);
	$Index->stop	= true;
	return;
}
if (!$client['active']) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'access_denied',
		'error_description'	=> 'Inactive client id'
	]);
	$Index->stop	= true;
	return;
}
if (!$client['domain']) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'unauthorized_client',
		'error_description'	=> 'Request method is not authored'
	]);
	$Index->stop	= true;
	return;
}
if ($_GET['grant_type'] != 'guest_token' && !isset($_GET['redirect_uri'])) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'invalid_request',
		'error_description'	=> 'redirect_uri parameter required'
	]);
	$Index->stop	= true;
	return;
} elseif ($_GET['grant_type'] != 'guest_token' && !preg_match("/^[^\/]+:\/\/$client[domain]/", urldecode($_GET['redirect_uri']))) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'invalid_request',
		'error_description'	=> 'Invalid redirect_uri parameter'
	]);
	$Index->stop	= true;
	return;
}
if (!in_array($_GET['grant_type'], ['authorization_code', 'refresh_token', 'guest_token'])) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'unsupported_grant_type',
		'error_description'	=> 'Specified grant type is not supported, only "authorization_code" or "refresh_token" types available'
	]);
	$Index->stop	= true;
	return;
}
/**
 * Tokens operations processing
 */
switch ($_GET['grant_type']) {
	case 'authorization_code':
		if (!isset($_GET['code'])) {
			code_header(400);
			$Page->Content	= _json_encode([
				'error'				=> 'invalid_request',
				'error_description'	=> 'code parameter required'
			]);
			$Index->stop	= true;
			return;
		}
		$token_data	= $OAuth2->get_code($_GET['code'], $client['id'], $client['secret'], urldecode($_GET['redirect_uri']));
		if (!$token_data) {
			code_header(403);
			$Page->Content	= _json_encode([
				'error'				=> 'server_error',
				'error_description'	=> 'Server can\'t get token data, try later'
			]);
			$Index->stop	= true;
			return;
		}
		if ($token_data['expires_in'] < 0) {
			code_header(403);
			$Page->Content	= _json_encode([
				'error'				=> 'access_denied',
				'error_description'	=> 'access_token expired'
			]);
			$Index->stop	= true;
			return;
		}
		$Page->Content	= _json_encode($token_data);
		$Index->stop	= true;
		return;
	case 'refresh_token':
		if (!isset($_GET['refresh_token'])) {
			code_header(400);
			$Page->Content	= _json_encode([
				'error'				=> 'refresh_token',
				'error_description'	=> 'refresh_token parameter required'
			]);
			$Index->stop	= true;
			return;
		}
		$token_data	= $OAuth2->refresh_token($_GET['refresh_token'], $client['id'], $client['secret']);
		if (!$token_data) {
			code_header(403);
			$Page->Content	= _json_encode([
				'error'				=> 'access_denied',
				'error_description'	=> 'User session invalid'
			]);
			$Index->stop	= true;
			return;
		}
		$Page->Content	= _json_encode($token_data);
		$Index->stop	= true;
		return;
	case 'guest_token':
		global $User, $Config;
		if ($User->user()) {
			code_header(403);
			$Page->Content	= _json_encode([
				'error'				=> 'access_denied',
				'error_description'	=> 'Only guests, not user allowed to access this grant_type'
			]);
			$Index->stop	= true;
			return;
		}
		if (!$Config->module('OAuth2')->guest_tokens) {
			code_header(403);
			$Page->Content	= _json_encode([
				'error'				=> 'access_denied',
				'error_description'	=> 'Guest tokens disabled'
			]);
			$Index->stop	= true;
		}
		$code	= $OAuth2->add_code($client['id'], 'code', '');
		if (!$code) {
			code_header(500);
			$Page->Content	= _json_encode([
				'error'				=> 'server_error',
				'error_description'	=> 'Server can\'t generate code, try later'
			]);
			$Index->stop	= true;
			return;
		}
		$token_data	= $OAuth2->get_code($code, $client['id'], $client['secret'], '');
		if ($token_data) {
			$Page->Content	= _json_encode($token_data);
			$Index->stop	= true;
			return;
		} else {
			code_header(500);
			$Page->Content	= _json_encode([
				'error'				=> 'server_error',
				'error_description'	=> 'Server can\'t get token data, try later'
			]);
			$Index->stop	= true;
			return;
		}
}