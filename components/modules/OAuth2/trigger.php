<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\OAuth2;
global $Core;
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($data) {
		global $Config;
		$module	= basename(__DIR__);
		if (!$Config->module($module)->active() && substr($data['rc'], 0, 5) == 'admin') {
			return;
		}
		global $Core;
		require_once __DIR__.'/OAuth2.php';
		$Core->create('_cs\\modules\\OAuth2\\OAuth2');
		$rc		= explode('/', $data['rc']);
		if (isset($rc[0]) && $rc[0] == $module) {
			if (isset($rc[1])) {
				$rc[1]	= explode('?', $rc[1], 2)[0];
			}
			$data['rc']	= implode('/', $rc);
			header('Cache-Control: no-store');
    		header('Pragma: no-cache');
		}
	}
);
$Core->register_trigger(
	'System/User/del_all_sessions',
	function ($data) {
		global $OAuth2;
		$OAuth2->del_access(0, $data['id']);
	}
);
$Core->register_trigger(
	'System/User/construct/before',
	function () {
		if (API && isset($_POST['client_id'], $_POST['access_token'])) {
			header('Cache-Control: no-store');
			header('Pragma: no-cache');
			global $OAuth2, $Page, $Core;
			if (!($client = $OAuth2->get_client($_GET['client_id']))) {
				code_header(400);
				$Page->Content	= _json_encode([
					'error'				=> 'access_denied',
					'error_description'	=> 'Invalid client id'
				]);
				interface_off();
				__finish();
			} elseif (!$client['active']) {
				code_header(403);
				header('Content-type: application/json');
				$Page->Content	= _json_encode([
					'error'				=> 'access_denied',
					'error_description'	=> 'Inactive client id'
				]);
				interface_off();
				__finish();
			}
			$_SERVER['HTTP_USER_AGENT']	= "OAuth2-$client[name]-$client[id]";
			if (isset($_POST['client_secret'])) {
				if ($_GET['client_secret'] != $client['secret']) {
					code_header(400);
					$Page->Content	= _json_encode([
						'error'				=> 'access_denied',
						'error_description'	=> 'client_secret do not corresponds client_id'
					]);
					interface_off();
					__finish();
				}
				$token_data	= $OAuth2->get_token($_POST['access_token'], $_POST['client_id'], $client['secret']);
			} else {
				$token_data	= $OAuth2->get_token($_POST['access_token'], $_POST['client_id'], $client['secret']);
				if ($token_data['type']	== 'code') {
					code_header(403);
					header('Content-type: application/json');
					$Page->Content	= _json_encode([
						'error'				=> 'invalid_request',
						'error_description'	=> 'This access_token can\'t be used without client_secret'
					]);
					interface_off();
					__finish();
				}
			}
			if ($token_data['expire_in'] < TIME) {
				code_header(403);
				header('Content-type: application/json');
				$Page->Content	= _json_encode([
					'error'				=> 'access_denied',
					'error_description'	=> 'access_token expired'
				]);
				interface_off();
				__finish();
			}
			$_POST['session']	= $token_data['session'];
			$Core->register_trigger(
				'System/User/construct/after',
				function () {
					global $User;
					if (!$User->user()) {
						global $Page;
						code_header(403);
						header('Content-type: application/json');
						$Page->Content	= _json_encode([
							'error'				=> 'access_denied',
							'error_description'	=> 'User session invalid'
						]);
						interface_off();
						__finish();
					}
				}
			);
		}
	}
);