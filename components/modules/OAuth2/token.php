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
header('Content-type: application/json');
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
if (!($client = $OAuth2->get_client($_GET['client_id']))) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'access_denied',
		'error_description'	=> 'Client id not found'
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
if (!isset($_GET['redirect_uri'])) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'invalid_request',
		'error_description'	=> 'redirect_uri parameter required'
	]);
	$Index->stop	= true;
	return;
} elseif (!preg_match("/^[^\/]+:\/\/$client[domain]/", $_GET['redirect_uri'])) {
	code_header(400);
	$Page->Content	= _json_encode([
		'error'				=> 'invalid_request',
		'error_description'	=> 'Invalid redirect_uri parameter'
	]);
	$Index->stop	= true;
	return;
}
if (!in_array($_GET['grant_type'], ['authorization_code', 'refresh_token'])) {
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
		$token_data	= $OAuth2->get_code($_GET['code'], $client['id'], $client['secret']);
		if (!$token_data) {
			code_header(403);
			$Page->Content	= _json_encode([
				'error'				=> 'server_error',
				'error_description'	=> 'Server can\'t get token data, try later'
			]);
			$Index->stop	= true;
			return;
		}
		if ($token_data['expire'] < 0) {
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
			header('Content-type: application/json');
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
}