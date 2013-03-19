<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
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
				strpos(urldecode($_GET['redirect_uri']), 'http://'.$client['domain']) !== 0 &&
				strpos(urldecode($_GET['redirect_uri']), 'https://'.$client['domain']) !== 0
			) {
				code_header(302);
				header('Location: '.http_build_url(
					urldecode($_GET['redirect_uri']),
					[
						'error'				=> 'invalid_request',
						'error_description'	=> 'Client id inactive'
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
		strpos(urldecode($_GET['redirect_uri']), 'http://'.$client['domain']) !== 0 &&
		strpos(urldecode($_GET['redirect_uri']), 'https://'.$client['domain']) !== 0
	) {
		code_header(400);
		$Page->Content	= '';
		$Page->warning($L->redirect_uri_parameter_invalid);
		$Index->stop	= true;
		return;
	}
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
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'allow':
			$OAuth2->set_access($client['id'], $User->id);//TODO realize method
		break;
		default:
			code_header(302);
			header('Location: '.http_build_url(
				urldecode($_GET['redirect_uri']),
				[
					'error'				=> 'access_denied',
					'error_description'	=> 'User denied access'
				]
			));
			$Page->Content	= '';
			$Index->stop	= true;
			return;
	}
}
if (!$OAuth2->get_access($client['id'], $User->id)) {//TODO realize method
	$Index->form			= true;
	$Index->buttons			= false;
	$Index->content(
		$L->clients_want_access_your_account($client['name'])
	);
	$Index->action			= $Config->base_url().'/'.$Config->server['raw_relative_url'];
	$Index->post_buttons	= h::{'button[type=submit][mode=allow]'}($L->allow).
							  h::{'button[type=submit][mode=deny]'}($L->deny);
}