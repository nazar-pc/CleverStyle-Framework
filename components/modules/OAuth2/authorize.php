<?php
/**
 * @package        OAuth2
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
/**
 * Provides next events:<br>
 *  OAuth2/custom_sign_in_page
 */
namespace cs\modules\OAuth2;

use
	h,
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Language\Prefix,
	cs\Page,
	cs\Route,
	cs\User;

if (!function_exists(__NAMESPACE__.'\\error_redirect')) {
	function error_redirect ($error, $description) {
		_header(
			'Location: '.http_build_url(
				urldecode($_GET['redirect_uri']),
				[
					'error'             => $error,
					'error_description' => $description,
					'state'             => isset($_GET['state']) ? $_GET['state'] : false
				]
			),
			true,
			302
		);
		interface_off();
	}
}
$OAuth2 = OAuth2::instance();
$Config = Config::instance();
$Index  = Index::instance();
$L      = new Prefix('oauth2_');
$Page   = Page::instance();
/**
 * Errors processing
 */
if (!isset($_GET['client_id'])) {
	error_redirect('invalid_request', 'client_id parameter required');
	return;
}
$client = $OAuth2->get_client($_GET['client_id']);
if (!$client) {
	error_redirect('invalid_request', 'client_id not found');
	return;
}
if (!$client['domain']) {
	error_code(400);
	$Page->error([
		'unauthorized_client',
		'Request method is not authored'
	]);
}
if (!$client['active']) {
	/**
	 * guest_token should return JSON data while all other works with redirects
	 */
	if ($_GET['response_type'] != 'guest_token') {
		if (!isset($_GET['redirect_uri'])) {
			status_code(400);
			$Page->error([
				'invalid_request',
				'Inactive client_id, redirect_uri parameter required'
			]);
		} else {
			if (
				urldecode($_GET['redirect_uri']) != $Config->base_url().'/OAuth2/blank/' &&
				!preg_match("/^[^\/]+:\/\/$client[domain]/", urldecode($_GET['redirect_uri']))
			) {
				error_redirect('access_denied', 'Inactive client id');
				return;
			} else {
				status_code(400);
				$Page->error([
					'invalid_request',
					'Inactive client_id, redirect_uri parameter required'
				]);
			}
		}
	} else {
		status_code(400);
		$Page->error([
			'invalid_request',
			'inactive client_id'
		], true);
	}
}
/**
 * guest_token should return JSON data while all other works with redirects
 */
if ($_GET['response_type'] != 'guest_token') {
	if (!isset($_GET['redirect_uri'])) {
		status_code(400);
		$Page->error([
			'invalid_request',
			'redirect_uri parameter required'
		]);
	} elseif (
		urldecode($_GET['redirect_uri']) != $Config->base_url().'/OAuth2/blank/' &&
		!preg_match("/^[^\/]+:\/\/$client[domain]/", urldecode($_GET['redirect_uri']))
	) {
		status_code(400);
		$Page->error([
			'invalid_request',
			'redirect_uri parameter invalid'
		]);
	}
	$redirect_uri = urldecode($_GET['redirect_uri']);
	if (!isset($_GET['response_type'])) {
		error_redirect('invalid_request', 'response_type parameter required');
		return;
	}
	if (!in_array($_GET['response_type'], ['code', 'token', 'guest_token'])) {
		error_redirect('unsupported_response_type', 'Specified response_type is not supported, only "token" or "code" or "guest_token" types available');
		return;
	}
} else {
	if (!isset($_GET['response_type'])) {
		$Page->error([
			'invalid_request',
			'response_type parameter required'
		], true);
	}
	if (!in_array($_GET['response_type'], ['code', 'token', 'guest_token'])) {
		$Page->error([
			'unsupported_response_type',
			'Specified response_type is not supported, only "token" or "code" or "guest_token" types available'
		], true);
	}
}
$User = User::instance();
if (!$User->user()) {
	if ($_GET['response_type'] != 'guest_token') {
		status_code(403);
		if (Event::instance()->fire('OAuth2/custom_sign_in_page')) {
			$Page->Content = '';
			$Page->warning($L->you_are_not_logged_in);
		}
		return;
	} elseif (!$Config->module('OAuth2')->guest_tokens) {
		error_redirect('access_denied', 'Guest tokens disabled');
		return;
	}
}
/**
 * Authorization processing
 */
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'allow':
			$OAuth2->add_access($client['id']);
			break;
		default:
			_header(
				'Location: '.http_build_url(
					urldecode($redirect_uri),
					[
						'error'             => 'access_denied',
						'error_description' => 'User denied access',
						'state'             => isset($_GET['state']) ? $_GET['state'] : false
					]
				),
				true,
				302
			);
			$Page->Content = '';
			return;
	}
}
if (!$OAuth2->get_access($client['id'])) {
	$Index->form    = true;
	$Index->buttons = false;
	$Page->success(
		$L->client_want_access_your_account($client['name'])
	);
	$Index->action         = $Config->base_url().'/'.Route::instance()->raw_relative_address;
	$Index->custom_buttons =
		h::{'button.uk-button[type=submit][name=mode][value=allow]'}($L->allow).
		h::{'button.uk-button[type=submit][mode=mode][value=deny]'}($L->deny);
	return;
}
$code = $OAuth2->add_code($client['id'], $_GET['response_type'], $redirect_uri);
if (!$code) {
	error_redirect('server_error', "Server can't generate code, try later");
	return;
}
switch ($_GET['response_type']) {
	case 'code':
		_header(
			'Location: '.http_build_url(
				urldecode($redirect_uri),
				[
					'code'  => $code,
					'state' => isset($_GET['state']) ? $_GET['state'] : false
				]
			),
			true,
			302
		);
		$Page->Content = '';
		return;
	case 'token':
		$token_data = $OAuth2->get_code($code, $client['id'], $client['secret'], $redirect_uri);
		if ($token_data) {
			unset($token_data['refresh_token']);
			_header(
				'Location: '.uri_for_token(
					http_build_url(
						urldecode($redirect_uri),
						array_merge(
							$token_data,
							[
								'state' => isset($_GET['state']) ? $_GET['state'] : false
							]
						)
					)
				),
				true,
				302
			);
			$Page->Content = '';
			return;
		} else {
			error_redirect('server_error', "Server can't get token data, try later");
			return;
		}
	case 'guest_token':
		_header('Cache-Control: no-store');
		_header('Pragma: no-cache');
		interface_off();
		if ($User->user()) {
			error_code(403);
			$Page->error([
				'access_denied',
				'Only guests, not users allowed to access this response_type'
			], true);
		}
		$code = $OAuth2->add_code($client['id'], 'token', urldecode($_GET['redirect_uri']));
		if (!$code) {
			error_code(500);
			$Page->error([
				'server_error',
				"Server can't generate code, try later"
			], true);
		}
		$token_data = $OAuth2->get_code($code, $client['id'], $client['secret'], urldecode($_GET['redirect_uri']));
		if ($token_data) {
			unset($token_data['refresh_token']);
			$Page->json($token_data);
			return;
		} else {
			error_code(500);
			$Page->error([
				'server_error',
				"Server can't get token data, try later"
			], true);
		}
}
