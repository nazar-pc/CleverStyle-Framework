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
	cs\Response;

Event::instance()
	->on(
		'System/Request/routing_replace',
		function ($data) {
			if (Config::instance()->module('OAuth2')->enabled() && strpos($data['rc'], 'admin') !== 0) {
				$rc = explode('/', $data['rc'], 2);
				if (isset($rc[0]) && $rc[0] == 'OAuth2') {
					if (isset($rc[1])) {
						$rc[1] = explode('?', $rc[1], 2)[0];
					}
					$data['rc'] = implode('/', $rc);
					Response::instance()
						->header('cache-control', 'no-store')
						->header('pragma', 'no-cache');
				}
				$POST = (array)$_POST;
				Event::instance()->on(
					'System/User/construct/after',
					function () use ($POST) {
						foreach ($POST as $i => $v) {
							$_POST[$i] = $v;
						}
					}
				);
			}
		}
	)
	->on(
		'System/Session/del_all',
		function ($data) {
			if (Config::instance()->module('OAuth2')->enabled()) {
				OAuth2::instance()->del_access(0, $data['id']);
			}
		}
	)
	->on(
		'System/User/construct/before',
		function () {
			if (!Config::instance()->module('OAuth2')->enabled()) {
				return;
			}
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
			/** @noinspection IfConditionalsWithoutCurvyBracketsInspection */
			if ($token_data['type'] == 'token') {
				// TODO: add some mark if this is client-side only token, so that it can be accounted by components
				// Also admin access should be blocked for client-side only tokens
			}
			$Request->headers['user-agent'] = "OAuth2-$client[name]-$client[id]";
			$Request->data['session']       = $token_data['session'];
			Response::instance()->cookie('session', $token_data['session'], 0, true);
		}
	)
	->on(
		'admin/System/components/modules/install/after',
		function ($data) {
			if ($data['name'] != 'OAuth2') {
				return;
			}
			Config::instance()->module('OAuth2')->set(
				[
					'expiration'             => 3600,
					'automatic_prolongation' => 1
				]
			);
		}
	);
