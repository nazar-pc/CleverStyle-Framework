<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\OAuth2;
use			cs\Config,
			cs\Page,
			cs\Trigger,
			cs\User;
Trigger::instance()->register(
	'System/User/del_all_sessions',
	function ($data) {
		OAuth2::instance()->del_access(0, $data['id']);
	}
);
Trigger::instance()->register(
	'System/User/construct/before',
	function () {
		/**
		 * Get client credentials from request body or headers and create corresponding local variables
		 */
		if (isset($_REQUEST['client_id'])) {
			$client_id	= $_REQUEST['client_id'];
		} elseif (isset($_SERVER['CLIENT_ID'])) {
			$client_id	= $_SERVER['CLIENT_ID'];
		}
		if (isset($_REQUEST['access_token'])) {
			$access_token	= $_REQUEST['access_token'];
		} elseif (isset($_SERVER['ACCESS_TOKEN'])) {
			$access_token	= $_SERVER['ACCESS_TOKEN'];
		}
		if (isset($_REQUEST['client_secret'])) {
			$client_secret	= $_REQUEST['client_secret'];
		} elseif (isset($_SERVER['CLIENT_SECRET'])) {
			$client_secret	= $_SERVER['CLIENT_SECRET'];
		}
		if (!(
			API && isset($client_id, $access_token)
		)) {
			return;
		}
		header('Cache-Control: no-store');
		header('Pragma: no-cache');
		$OAuth2						= OAuth2::instance();
		$Page						= Page::instance();
		if (!($client = $OAuth2->get_client($client_id))) {
			code_header(400);
			$Page->json([
				'error'				=> 'access_denied',
				'error_description'	=> 'Invalid client id'
			]);
			exit;
		} elseif (!$client['active']) {
			code_header(403);
			$Page->json([
				'error'				=> 'access_denied',
				'error_description'	=> 'Inactive client id'
			]);
			exit;
		}
		$_SERVER['HTTP_USER_AGENT']	= "OAuth2-$client[name]-$client[id]";
		if (isset($client_secret)) {
			if ($client_secret != $client['secret']) {
				code_header(400);
				$Page->json([
					'error'				=> 'access_denied',
					'error_description'	=> 'client_secret do not corresponds client_id'
				]);
				exit;
			}
			$token_data	= $OAuth2->get_token($access_token, $client_id, $client['secret']);
		} else {
			$token_data	= $OAuth2->get_token($access_token, $client_id, $client['secret']);
			if ($token_data['type']	== 'code') {
				code_header(403);
				$Page->json([
					'error'				=> 'invalid_request',
					'error_description'	=> 'This access_token can\'t be used without client_secret'
				]);
				exit;
			}
		}
		if (!$token_data) {
			code_header(403);
			$Page->json([
				'error'				=> 'access_denied',
				'error_description'	=> 'access_token expired'
			]);
			exit;
		}
		$_POST['session']	= $_REQUEST['session']	= $token_data['session'];
		if (!Config::instance()->module('OAuth2')->guest_tokens) {
			Trigger::instance()->register(
				'System/User/construct/after',
				function () {
					if (!User::instance()->user()) {
						code_header(403);
						Page::instance()->json([
							'error'				=> 'access_denied',
							'error_description'	=> 'Guest tokens disabled'
						]);
						exit;
					}
				}
			);
		}
	}
);
Trigger::instance()->register(
	'System/Index/mainmenu',
	function ($data) {
		if ($data['path'] == 'OAuth2') {
			$data['hide']	= true;
			return false;
		}
		return true;
	}
);