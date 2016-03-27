<?php
/**
 * @package   OAuth2
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Provides next events:<br>
 *  OAuth2/custom_sign_in_page
 *  OAuth2/custom_allow_access_page
 */
namespace cs\modules\OAuth2;
use
	h,
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language\Prefix,
	cs\Page,
	cs\Response,
	cs\User;

if (!function_exists(__NAMESPACE__.'\\error_redirect')) {
	function error_redirect ($error, $description) {
		Response::instance()->redirect(
			http_build_url(
				urldecode($_GET['redirect_uri']),
				[
					'error'             => $error,
					'error_description' => $description,
					'state'             => isset($_GET['state']) ? $_GET['state'] : false
				]
			),
			302
		);
		Page::instance()->interface = false;
	}
}
$OAuth2 = OAuth2::instance();
$Config = Config::instance();
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
if (!$client['active']) {
	if (!isset($_GET['redirect_uri'])) {
		$e = new ExitException(
			[
				'invalid_request',
				'Inactive client_id, redirect_uri parameter required'
			],
			400
		);
		$e->setJson();
		throw $e;
	} else {
		if (
			urldecode($_GET['redirect_uri']) != $Config->base_url().'/OAuth2/blank/' &&
			!preg_match("#^[^/]+://$client[domain]#", urldecode($_GET['redirect_uri']))
		) {
			error_redirect('access_denied', 'Inactive client id');
			return;
		} else {
			$e = new ExitException(
				[
					'invalid_request',
					'Inactive client_id, redirect_uri parameter required'
				],
				400
			);
			$e->setJson();
			throw $e;
		}
	}
}
if (!isset($_GET['redirect_uri'])) {
	throw new ExitException(
		[
			'invalid_request',
			'redirect_uri parameter required'
		],
		400
	);
} elseif (
	urldecode($_GET['redirect_uri']) != $Config->base_url().'/OAuth2/blank/' &&
	!preg_match("#^[^/]+://$client[domain]#", urldecode($_GET['redirect_uri']))
) {
	throw new ExitException(
		[
			'invalid_request',
			'redirect_uri parameter invalid'
		],
		400
	);
}
$redirect_uri = urldecode($_GET['redirect_uri']);
if (!isset($_GET['response_type'])) {
	error_redirect('invalid_request', 'response_type parameter required');
	return;
}
$User = User::instance();
if (!$User->user()) {
	if (Event::instance()->fire('OAuth2/custom_sign_in_page')) {
		$Page->Content = '';
		$Page->warning($L->you_are_not_logged_in);
	}
	return;
}
$Response = Response::instance();
/**
 * Authorization processing
 */
if (isset($_POST['mode'])) {
	if ($_POST['mode'] == 'allow') {
		$OAuth2->add_access($client['id']);
	} else {
		$Response->redirect(
			http_build_url(
				urldecode($redirect_uri),
				[
					'error'             => 'access_denied',
					'error_description' => 'User denied access',
					'state'             => isset($_GET['state']) ? $_GET['state'] : false
				]
			),
			302
		);
		$Page->Content = '';
		return;
	}
}
if (!$OAuth2->get_access($client['id'])) {
	if (Event::instance()->fire('OAuth2/custom_allow_access_page')) {
		$Page->success(
			$L->client_want_access_your_account($client['name'])
		);
		$Page->content(
			h::form(
				h::{'button[is=cs-button][type=submit][name=mode][value=allow]'}($L->allow).
				h::{'button[is=cs-button][type=submit][mode=mode][value=deny]'}($L->deny)
			)
		);
	}
	return;
}
$code = $OAuth2->add_code($client['id'], $_GET['response_type'], $redirect_uri);
if (!$code) {
	error_redirect('server_error', "Server can't generate code, try later");
	return;
}
switch ($_GET['response_type']) {
	case 'code':
		$Response->redirect(
			http_build_url(
				urldecode($redirect_uri),
				[
					'code'  => $code,
					'state' => isset($_GET['state']) ? $_GET['state'] : false
				]
			),
			302
		);
		$Page->Content = '';
		return;
	case 'token':
		$token_data = $OAuth2->get_code($code, $client['id'], $client['secret'], $redirect_uri);
		if ($token_data) {
			unset($token_data['refresh_token']);
			$Response->redirect(
				uri_for_token(
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
				302
			);
			$Page->Content = '';
			return;
		} else {
			error_redirect('server_error', "Server can't get token data, try later");
			return;
		}
	default:
		error_redirect('unsupported_response_type', 'Specified response_type is not supported, only "token" or "code" types available');
}
