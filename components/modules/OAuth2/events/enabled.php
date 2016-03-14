<?php
/**
 * @package   OAuth2
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\OAuth2;

use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Request,
	cs\Response,
	cs\User;

Event::instance()
	->on(
		'System/Session/del_all',
		function ($data) {
			OAuth2::instance()->del_access(0, $data['id']);
		}
	)
	->on(
		'System/User/construct/before',
		function () {
			$Request = Request::instance();
			/**
			 * Works only for API requests
			 */
			if (!$Request->api_path) {
				return;
			}
			if (preg_match('/Bearer ([0-9a-z]{32})/i', $Request->header('authorization'), $access_token)) {
				$access_token = $access_token[1];
			} else {
				return;
			}
			$OAuth2     = OAuth2::instance();
			$token_data = $OAuth2->get_token($access_token);
			if (!$token_data) {
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
			$client = $OAuth2->get_client($token_data['client_id']);
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
			} elseif (!$client['active']) {
				$e = new ExitException(
					[
						'access_denied',
						'Inactive client id'
					],
					403
				);
				$e->setJson();
				throw $e;
			}
			if ($token_data['type'] == 'token') {
				// TODO: add some mark if this is client-side only token, so that it can be accounted by components
				// Also ADMIN access should be blocked for client-side only tokens
			}
			$Request->user_agent = "OAuth2-$client[name]-$client[id]";
			$_POST['session']    = $token_data['session'];
			$_REQUEST['session'] = $token_data['session'];
			Response::instance()->cookie('session', $token_data['session'], 0, true);
			if (!Config::instance()->module('OAuth2')->guest_tokens) {
				Event::instance()->on(
					'System/User/construct/after',
					function () {
						if (!User::instance()->user()) {
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
					}
				);
			}
		}
	);
