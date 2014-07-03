<?php
/**
 * @package        OAuth2
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  OAuth2/custom_sign_in_page
 */
namespace cs\modules\OAuth2;

use
	h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page,
	cs\Trigger,
	cs\User;

$OAuth2 = OAuth2::instance();
$Config = Config::instance();
$Index  = Index::instance();
$L      = new Prefix('oauth2_');
$Page   = Page::instance();
/**
 * Errors processing
 */
if (!isset($_GET['client_id'])) {
	code_header(400);
	$Page->Content = '';
	$Page->warning($L->client_id_parameter_required);
	return;
}
$client = $OAuth2->get_client($_GET['client_id']);
if (!$client) {
	code_header(400);
	$Page->Content = '';
	$Page->warning($L->client_id_not_found);
	return;
}
if (!$client['domain']) {
	error_code(400);
	$Page->error([
		'unauthorized_client',
		'Request method is not authored'
	], true);
}
if (!$client['active']) {
	if ($_GET['response_type'] != 'guest_token') {
		if (!isset($_GET['redirect_uri'])) {
			code_header(400);
			$Page->Content = '';
			$Page->warning($L->inactive_client_id);
			$Page->warning($L->redirect_uri_parameter_required);
			return;
		} else {
			if (
				urldecode($_GET['redirect_uri']) != $Config->base_url().'/OAuth2/blank/' &&
				!preg_match("/^[^\/]+:\/\/$client[domain]/", urldecode($_GET['redirect_uri']))
			) {
				header(
					'Location: '.http_build_url(
						urldecode($_GET['redirect_uri']),
						[
							'error'             => 'access_denied',
							'error_description' => 'Inactive client id',
							'state'             => isset($_GET['state']) ? $_GET['state'] : false
						]
					),
					true,
					302
				);
				$Page->Content = '';
				return;
			} else {
				code_header(400);
				$Page->Content = '';
				$Page->warning($L->inactive_client_id);
				$Page->warning($L->redirect_uri_parameter_invalid);
				return;
			}
		}
	} else {
		code_header(400);
		$Page->Content = '';
		$Page->warning($L->inactive_client_id);
		return;
	}
}
if (!isset($_GET['redirect_uri'])) {
	code_header(400);
	$Page->Content = '';
	$Page->warning($L->redirect_uri_parameter_required);
	return;
} elseif (
	urldecode($_GET['redirect_uri']) != $Config->base_url().'/OAuth2/blank/' &&
	!preg_match("/^[^\/]+:\/\/$client[domain]/", urldecode($_GET['redirect_uri']))
) {
	code_header(400);
	$Page->Content = '';
	$Page->warning($L->redirect_uri_parameter_invalid);
	return;
}
$redirect_uri = urldecode($_GET['redirect_uri']);
if (!isset($_GET['response_type'])) {
	header(
		'Location: '.http_build_url(
			urldecode($redirect_uri),
			[
				'error'             => 'invalid_request',
				'error_description' => 'response_type parameter required',
				'state'             => isset($_GET['state']) ? $_GET['state'] : false
			]
		),
		true,
		302
	);
	$Page->Content = '';
	return;
}
if (!in_array($_GET['response_type'], ['code', 'token', 'guest_token'])) {
	header(
		'Location: '.http_build_url(
			urldecode($redirect_uri),
			[
				'error'             => 'unsupported_response_type',
				'error_description' => 'Specified response type is not supported, only "token" or "code" types available',
				'state'             => isset($_GET['state']) ? $_GET['state'] : false
			]
		),
		true,
		302
	);
	$Page->Content = '';
	return;
}
$User = User::instance();
if (!$User->user()) {
	if ($_GET['response_type'] != 'guest_token') {
		code_header(403);
		if (Trigger::instance()->run('OAuth2/custom_sign_in_page')) {
			$Page->Content = '';
			$Page->warning($L->you_are_not_logged_in);
		}
		return;
	} elseif (!$Config->module('OAuth2')->guest_tokens) {
		header(
			'Location: '.http_build_url(
				urldecode($redirect_uri),
				[
					'error'             => 'access_denied',
					'error_description' => 'Guest tokens disabled',
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
/**
 * Authorization processing
 */
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'allow':
			$OAuth2->add_access($client['id']);
			break;
		default:
			header(
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
	$Index->action       = $Config->base_url().'/'.$Config->server['raw_relative_address'];
	$Index->post_buttons = h::{'button[type=submit][name=mode][value=allow]'}($L->allow).
		h::{'button[type=submit][mode=mode][value=deny]'}($L->deny);
} else {
	$code = $OAuth2->add_code($client['id'], $_GET['response_type'], $redirect_uri);
	if (!$code) {
		header(
			'Location: '.http_build_url(
				urldecode($redirect_uri),
				[
					'error'             => 'server_error',
					'error_description' => "Server can't generate code, try later",
					'state'             => isset($_GET['state']) ? $_GET['state'] : false
				]
			),
			true,
			302
		);
		$Page->Content = '';
		return;
	}
	switch ($_GET['response_type']) {
		case 'code':
			header(
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
				header(
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
				header(
					'Location: '.uri_for_token(
						http_build_url(
							urldecode($redirect_uri),
							[
								'error'             => 'server_error',
								'error_description' => "Server can't get token data, try later",
								'state'             => isset($_GET['state']) ? $_GET['state'] : false
							]
						)
					),
					true,
					302
				);
				$Page->Content = '';
				return;
			}
		case 'guest_token':
			header('Cache-Control: no-store');
			header('Pragma: no-cache');
			interface_off();
			if ($User->user()) {
				error_code(403);
				$Page->error([
					'access_denied',
					'Only guests, not user allowed to access this response_type'
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
				header(
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
				error_code(500);
				$Page->error([
					'server_error',
					"Server can't get token data, try later"
				], true);
			}
	}
}
