<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $Page, $User, $db, $Key, $L;
/**
 * If AJAX request from local referer, user is guest, login attempts count is satisfactory,
 * user is active, not blocked - process authentication, otherwise - show error
 */
if (!$Config->server['referer']['local'] || !$Config->server['ajax']) {
	sleep(1);
	define('ERROR_CODE', 403);
	return;
} elseif (!$User->guest()) {
	$Page->json('reload');
	return;
} elseif ($Config->core['login_attempts_block_count'] && $User->login_attempts() >= $Config->core['login_attempts_block_count']) {
	$User->login_result(false);
	define('ERROR_CODE', 403);
	$Page->error($L->login_attempts_ends_try_after.' '.format_time($Config->core['login_attempts_block_time']));
	sleep(1);
	return;
}
/**
 * First step - user searching by login, generation of random hash for second step, creating of temporary key
 */
if (
	isset($_POST['login']) &&
	!empty($_POST['login']) &&
	!isset($_POST['auth_hash']) &&
	($id = $User->get_id($_POST['login'])) &&
	$id != 1
) {
	$_POST['login']	= mb_strtolower($_POST['login']);
	if ($User->get('status', $id) == -1) {
		define('ERROR_CODE', 403);
		$Page->error($L->your_account_is_not_active);
		sleep(1);
		return;
	} elseif ($User->get('status', $id) == 0) {
		define('ERROR_CODE', 403);
		$Page->error($L->your_account_disabled);
		sleep(1);
		return;
	}
	$random_hash	= hash('sha224', MICROTIME);
	if ($Key->add(
		$Config->module('System')->db('keys'),
		hash('sha224', $User->ip.$User->user_agent.$_POST['login']),
		[
			'random_hash'	=> $random_hash,
			'login'			=> $_POST['login'],
			'id'			=> $id
		]
	)) {
		$Page->json($random_hash);
	} else {
		define('ERROR_CODE', 500);
		$Page->json($L->auth_server_error);
	}
	unset($random_hash);
/**
 * Second step - checking of authentication hash, session creating
 */
} elseif (isset($_POST['login'], $_POST['auth_hash'])) {
	$_POST['login']	= mb_strtolower($_POST['login']);
	$key_data = $Key->get(
		$Config->module('System')->db('keys'),
		hash('sha224', $User->ip.$User->user_agent.$_POST['login']),
		true
	);
	$auth_hash		= hash(
		'sha512',
		$key_data['login'].
		$User->get('password_hash', $key_data['id']).
		$User->user_agent.
		$key_data['random_hash']
	);
	if ($_POST['auth_hash'] == $auth_hash) {
		$User->add_session($key_data['id']);
		$User->login_result(true);
		$Page->json('reload');
	} else {
		$User->login_result(false);
		define('ERROR_CODE', 400);
		$content	= $L->auth_error_login;
		if (
			$Config->core['login_attempts_block_count'] &&
			$User->login_attempts() >= floor($Config->core['login_attempts_block_count'] * 2 / 3)
		) {
			$content	.= ' '.$L->login_attempts_left.' '.($Config->core['login_attempts_block_count'] - $User->login_attempts());
			sleep(1);
		} elseif (!$Config->core['login_attempts_block_count']) {
			sleep($User->login_attempts()*0.5);
		}
		$Page->error($content);
		unset($content);
	}
	unset($key_data, $auth_hash);
} else {
	$User->login_result(false);
	define('ERROR_CODE', 400);
	$content	= $L->auth_error_login;
	if (
		$Config->core['login_attempts_block_count'] &&
		$User->login_attempts() >= $Config->core['login_attempts_block_count'] * 2 / 3
	) {
		$content	.= ' '.$L->login_attempts_left.' '.($Config->core['login_attempts_block_count'] - $User->login_attempts());
		sleep(1);
	} elseif (!$Config->core['login_attempts_block_count'] && $User->login_attempts() > 3) {
		sleep($User->login_attempts()*0.5);
	}
	$Page->error($content);
	unset($content);
}