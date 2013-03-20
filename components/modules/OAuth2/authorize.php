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
global $Page, $OAuth2, $L, $Index, $Config, $User;
/**
 * Errors processing
 */
if (!isset($_GET['client_id'])) {
	code_header(400);
	$Page->Content	= '';
	$Page->warning($L->client_id_parameter_required);
	$Index->stop	= true;
	return;
}
if (!($client = $OAuth2->get_client($_GET['client_id']))) {
	code_header(400);
	$Page->Content	= '';
	$Page->warning($L->client_id_not_found);
	$Index->stop	= true;
	return;
}
if (!$client['active']) {
	if ($client['domain']) {
		if (!isset($_GET['redirect_uri'])) {
			code_header(400);
			$Page->Content	= '';
			$Page->warning($L->client_id_inactive);
			$Page->warning($L->redirect_uri_parameter_required);
			$Index->stop	= true;
			return;
		} else {
			if (
				$_GET['redirect_uri'] != $Config->base_url().'/'.MODULE.'/blank/' &&
				!preg_match("/^[^\/]+:\/\/$client[domain]/", $_GET['redirect_uri'])
			) {
				code_header(302);
				header('Location: '.http_build_url(
					urldecode($_GET['redirect_uri']),
					[
						'error'				=> 'access_denied',
						'error_description'	=> 'Inactive client id',
						'state'				=> isset($_GET['state']) ? $_GET['state'] : false
					]
				));
				$Page->Content	= '';
				$Index->stop	= true;
				return;
			} else {
				code_header(400);
				$Page->Content	= '';
				$Page->warning($L->client_id_inactive);
				$Page->warning($L->redirect_uri_parameter_invalid);
				$Index->stop	= true;
				return;
			}
		}
	} else {
		code_header(400);
		$Page->Content	= '';
		$Page->warning($L->client_id_inactive);
		$Index->stop	= true;
		return;
	}
}
if ($client['domain']) {
	if (!isset($_GET['redirect_uri'])) {
		code_header(400);
		$Page->Content	= '';
		$Page->warning($L->redirect_uri_parameter_required);
		$Index->stop	= true;
		return;
	} elseif (
		$_GET['redirect_uri'] != $Config->base_url().'/'.MODULE.'/blank/' &&
		!preg_match("/^[^\/]+:\/\/$client[domain]/", $_GET['redirect_uri'])
	) {
		code_header(400);
		$Page->Content	= '';
		$Page->warning($L->redirect_uri_parameter_invalid);
		$Index->stop	= true;
		return;
	}
}
$redirect_uri	= isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : $Config->base_url().'/'.MODULE.'/blank/';
if (!isset($_GET['response_type'])) {
	code_header(302);
	header('Location: '.http_build_url(
		urldecode($redirect_uri),
		[
			'error'				=> 'invalid_request',
			'error_description'	=> 'response_type parameter required',
			'state'				=> isset($_GET['state']) ? $_GET['state'] : false
		]
	));
	$Page->Content	= '';
	$Index->stop	= true;
	return;
}
if (!in_array($_GET['response_type'], ['code', 'token'])) {
	code_header(302);
	header('Location: '.http_build_url(
		urldecode($redirect_uri),
		[
			'error'				=> 'unsupported_response_type',
			'error_description'	=> 'Specified response type is not supported, only "token" or "code" types available',
			'state'				=> isset($_GET['state']) ? $_GET['state'] : false
		]
	));
	$Page->Content	= '';
	$Index->stop	= true;
	return;
}
if (!$User->user()) {
	code_header(403);
	$Page->Content	= '';
	$Page->warning($L->you_are_not_logged_in);//TODO log in first to access this page
	$Index->stop	= true;
	return;
}
/**
 * Authorization processing
 */
$client			= $OAuth2->get_client($_GET['client_id']);
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'allow':
			$OAuth2->add_access($client['id']);
		break;
		default:
			code_header(302);
			header('Location: '.http_build_url(
				urldecode($redirect_uri),
				[
					'error'				=> 'access_denied',
					'error_description'	=> 'User denied access',
					'state'				=> isset($_GET['state']) ? $_GET['state'] : false
				]
			));
			$Page->Content	= '';
			$Index->stop	= true;
			return;
	}
}
if (!$OAuth2->get_access($client['id'])) {
	$Index->form			= true;
	$Index->buttons			= false;
	$Index->content(
		$L->clients_want_access_your_account($client['name'])
	);
	$Index->action			= $Config->base_url().'/'.$Config->server['raw_relative_url'];
	$Index->post_buttons	= h::{'button[type=submit][mode=allow]'}($L->allow).
							  h::{'button[type=submit][mode=deny]'}($L->deny);
} else {
	$code	= $OAuth2->add_code($client['id'], $_GET['response_type'], $redirect_uri);
	if (!$code) {
		code_header(302);
		header('Location: '.http_build_url(
			urldecode($redirect_uri),
			[
				'error'				=> 'server_error',
				'error_description'	=> 'Server can\'t generate code, try later',
				'state'				=> isset($_GET['state']) ? $_GET['state'] : false
			]
		));
		$Page->Content	= '';
		$Index->stop	= true;
		return;
	}
	switch ($_GET['response_type']) {
		case 'code':
			code_header(302);
			header('Location: '.http_build_url(
				urldecode($redirect_uri),
				[
					'code'	=> $code,
					'state'	=> isset($_GET['state']) ? $_GET['state'] : false
				]
			));
			$Page->Content	= '';
			$Index->stop	= true;
			return;
		case 'token':
			code_header(302);
			$token_data	= $OAuth2->get_code($code, $client['id'], $client['secret']);
			if ($token_data) {
				unset($token_data['refresh_token']);
				header('Location: '.uri_for_token(
					http_build_url(
						urldecode($redirect_uri),
						array_merge(
							$token_data,
							[
								'state'	=> isset($_GET['state']) ? $_GET['state'] : false
							]
						)
					)
				));
				$Page->Content	= '';
				$Index->stop	= true;
				return;
			} else {
				code_header(302);
				header('Location: '.uri_for_token(
					http_build_url(
						urldecode($redirect_uri),
						[
							'error'				=> 'server_error',
							'error_description'	=> 'Server can\'t get token data, try later',
							'state'				=> isset($_GET['state']) ? $_GET['state'] : false
						]
					)
				));
				$Page->Content	= '';
				$Index->stop	= true;
				return;
			}
	}
}