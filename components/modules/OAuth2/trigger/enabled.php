<?php
/**
 * @package        OAuth2
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\OAuth2;

use
	cs\Config,
	cs\Page,
	cs\Trigger,
	cs\User;

Trigger::instance()
	->register(
		'System/User/del_all_sessions',
		function ($data) {
			OAuth2::instance()->del_access(0, $data['id']);
		}
	)
	->register(
		'System/User/construct/before',
		function () {
			/**
			 * Get client credentials from request body or headers and create corresponding local variables
			 */
			if (isset($_REQUEST['client_id'])) {
				$client_id = $_REQUEST['client_id'];
			} elseif (isset($_SERVER['HTTP_CLIENT_ID'])) {
				$client_id = $_SERVER['HTTP_CLIENT_ID'];
			}
			if (isset($_REQUEST['access_token'])) {
				$access_token = $_REQUEST['access_token'];
			} elseif (isset($_SERVER['HTTP_ACCESS_TOKEN'])) {
				$access_token = $_SERVER['HTTP_ACCESS_TOKEN'];
			}
			if (isset($_REQUEST['client_secret'])) {
				$client_secret = $_REQUEST['client_secret'];
			} elseif (isset($_SERVER['HTTP_CLIENT_SECRET'])) {
				$client_secret = $_SERVER['HTTP_CLIENT_SECRET'];
			}
			if (!isset($client_id, $access_token)) {
				return;
			}
			$OAuth2 = OAuth2::instance();
			$Page   = Page::instance();
			$client = $OAuth2->get_client($client_id);
			if (!$client) {
				error_code(400);
				$Page->error([
					'access_denied',
					'Invalid client id'
				]);
			} elseif (!$client['active']) {
				error_code(403);
				$Page->error([
					'access_denied',
					'Inactive client id'
				]);
			}
			$_SERVER['HTTP_USER_AGENT'] = "OAuth2-$client[name]-$client[id]";
			if (isset($client_secret)) {
				if ($client_secret != $client['secret']) {
					error_code(400);
					$Page->error([
						'access_denied',
						'client_secret do not corresponds client_id'
					]);
				}
				$token_data = $OAuth2->get_token($access_token, $client_id, $client['secret']);
			} else {
				$token_data = $OAuth2->get_token($access_token, $client_id, $client['secret']);
				if ($token_data['type'] == 'code') {
					error_code(403);
					$Page->error([
						'invalid_request',
						"This access_token can't be used without client_secret"
					]);
				}
			}
			if (!$token_data) {
				error_code(403);
				$Page->error([
					'access_denied',
					'access_token expired'
				]);
			}
			$_POST['session'] = $_REQUEST['session'] = $token_data['session'];
			_setcookie('session', $token_data['session']);
			if (!Config::instance()->module('OAuth2')->guest_tokens) {
				Trigger::instance()->register(
					'System/User/construct/after',
					function () {
						if (!User::instance()->user()) {
							error_code(403);
							Page::instance()->error([
								'access_denied',
								'Guest tokens disabled'
							]);
						}
					}
				);
			}
		}
	);
