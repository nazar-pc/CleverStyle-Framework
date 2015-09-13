<?php
/**
 * @package   OAuth2
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\OAuth2;

use
	cs\Config,
	cs\ExitException,
	cs\Page,
	cs\User;

_header('Cache-Control: no-store');
_header('Pragma: no-cache');
interface_off();
/**
 * Errors processing
 */
$Config = Config::instance();
$Page   = Page::instance();
if (!isset($_POST['grant_type'])) {
	$e = new ExitException(
		[
			'invalid_request',
			'grant_type parameter required'
		],
		400
	);
	$e->setJson();
	throw $e;
}
if (!isset($_POST['client_id'])) {
	$e = new ExitException(
		[
			'invalid_request',
			'client_id parameter required'
		],
		400
	);
	$e->setJson();
	throw $e;
}
if (!isset($_POST['client_secret'])) {
	$e = new ExitException(
		[
			'invalid_request',
			'client_secret parameter required'
		],
		400
	);
	$e->setJson();
	throw $e;
}
$OAuth2 = OAuth2::instance();
$client = $OAuth2->get_client($_POST['client_id']);
if (!$client) {
	$e = new ExitException(
		[
			'access_denied',
			'Invalid client id'
		],
		400
	);
	$e->setJson();
	throw $e;
}
if (!$client['active']) {
	$e = new ExitException(
		[
			'access_denied',
			'Inactive client id'
		],
		400
	);
	$e->setJson();
	throw $e;
}
if ($_POST['client_secret'] != $client['secret']) {
	$e = new ExitException(
		[
			'access_denied',
			'client_secret do not corresponds client_id'
		],
		400
	);
	$e->setJson();
	throw $e;
}
if (!$client['domain']) {
	$e = new ExitException(
		[
			'unauthorized_client',
			'Request method is not authored'
		],
		400
	);
	$e->setJson();
	throw $e;
}
if ($_POST['grant_type'] == 'authorization_code') {
	if (!isset($_POST['redirect_uri'])) {
		$e = new ExitException(
			[
				'invalid_request',
				'redirect_uri parameter required'
			],
			400
		);
		$e->setJson();
		throw $e;
	} elseif (
		urldecode($_POST['redirect_uri']) != $Config->base_url().'/OAuth2/blank/' &&
		!preg_match("/^[^\/]+:\/\/$client[domain]/", urldecode($_POST['redirect_uri']))
	) {
		$e = new ExitException(
			[
				'invalid_request',
				'Invalid redirect_uri parameter'
			],
			400
		);
		$e->setJson();
		throw $e;
	}
}
if (!in_array($_POST['grant_type'], ['authorization_code', 'refresh_token', 'guest_token'])) {
	$e = new ExitException(
		[
			'unsupported_grant_type',
			'Specified grant type is not supported, only "authorization_code" or "refresh_token" types available'
		],
		400
	);
	$e->setJson();
	throw $e;
}
/**
 * Tokens operations processing
 */
switch ($_POST['grant_type']) {
	case 'authorization_code':
		if (!isset($_POST['code'])) {
			$e = new ExitException(
				[
					'invalid_request',
					'code parameter required'
				],
				400
			);
			$e->setJson();
			throw $e;
		}
		$token_data = $OAuth2->get_code($_POST['code'], $client['id'], $client['secret'], urldecode($_POST['redirect_uri']));
		if (!$token_data) {
			$e = new ExitException(
				[
					'server_error',
					"Server can't get token data, try later"
				],
				403
			);
			$e->setJson();
			throw $e;
		}
		if ($token_data['expires_in'] < 0) {
			$e = new ExitException(
				[
					'access_denied',
					'access_token expired'
				],
				403
			);
			$e->setJson();
			throw $e;
		}
		$Page->json($token_data);
		return;
	case 'refresh_token':
		if (!isset($_POST['refresh_token'])) {
			$e = new ExitException(
				[
					'invalid_request',
					'refresh_token parameter required'
				],
				400
			);
			$e->setJson();
			throw $e;
		}
		$token_data = $OAuth2->refresh_token($_POST['refresh_token'], $client['id'], $client['secret']);
		if (!$token_data) {
			$e = new ExitException(
				[
					'access_denied',
					'User session invalid'
				],
				403
			);
			$e->setJson();
			throw $e;
		}
		$Page->json($token_data);
		return;
	case 'guest_token':
		if (User::instance()->user()) {
			$e = new ExitException(
				[
					'access_denied',
					'Only guests, not user allowed to access this grant_type'
				],
				403
			);
			$e->setJson();
			throw $e;
		}
		if (!$Config->module('OAuth2')->guest_tokens) {
			$e = new ExitException(
				[
					'access_denied',
					'Guest tokens disabled'
				],
				403
			);
			$e->setJson();
			throw $e;
		}
		$code = $OAuth2->add_code($client['id'], 'code', '');
		if (!$code) {
			$e = new ExitException(
				[
					'server_error',
					"Server can't generate code, try later"
				],
				500
			);
			$e->setJson();
			throw $e;
		}
		$token_data = $OAuth2->get_code($code, $client['id'], $client['secret'], '');
		if ($token_data) {
			unset($token_data['refresh_token']);
			$Page->json($token_data);
			return;
		} else {
			$e = new ExitException(
				[
					'server_error',
					"Server can't get token data, try later"
				],
				500
			);
			$e->setJson();
			throw $e;
		}
}
