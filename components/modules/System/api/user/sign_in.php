<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$User	= User::instance();
/**
 * If AJAX request from local referer, user is guest, login attempts count is satisfactory,
 * user is active, not blocked - process authentication, otherwise - show error
 */
if (!(
	$Config->server['referer']['local'] &&
	$Config->server['ajax']
)) {
	sleep(1);
	error_code(403);
	return;
} elseif (!$User->guest()) {
	$Page->json('reload');
	return;
} elseif ($Config->core['sign_in_attempts_block_count'] && $User->get_sign_in_attempts_count() >= $Config->core['sign_in_attempts_block_count']) {
	$User->sign_in_result(false);
	error_code(403);
	$Page->error("$L->sign_in_attempts_ends_try_after ".format_time($Config->core['sign_in_attempts_block_time']));
	sleep(1);
	return;
}
/**
 * First step - user searching by login, generation of random hash for second step, creating of temporary key
 */
$Key	= Key::instance();
if (
	isset($_POST['login']) &&
	!empty($_POST['login']) &&
	!isset($_POST['auth_hash']) &&
	($id = $User->get_id($_POST['login'])) &&
	$id != User::GUEST_ID
) {
	$_POST['login']	= mb_strtolower($_POST['login']);
	if ($User->get('status', $id) == User::STATUS_NOT_ACTIVATED) {
		error_code(403);
		$Page->error($L->your_account_is_not_active);
		sleep(1);
		return;
	} elseif ($User->get('status', $id) == User::STATUS_INACTIVE) {
		error_code(403);
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
		error_code(500);
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
		$User->sign_in_result(true);
		$Page->json('reload');
	} else {
		$User->sign_in_result(false);
		error_code(400);
		$content	= $L->auth_error_sign_in;
		if (
			$Config->core['sign_in_attempts_block_count'] &&
			$User->get_sign_in_attempts_count() >= floor($Config->core['sign_in_attempts_block_count'] * 2 / 3)
		) {
			$content	.= " $L->sign_in_attempts_left ".($Config->core['sign_in_attempts_block_count'] - $User->get_sign_in_attempts_count());
			sleep(1);
		} elseif (!$Config->core['sign_in_attempts_block_count']) {
			sleep($User->get_sign_in_attempts_count() * 0.5);
		}
		$Page->error($content);
		unset($content);
	}
	unset($key_data, $auth_hash);
} else {
	$User->sign_in_result(false);
	error_code(400);
	$content	= $L->auth_error_sign_in;
	if (
		$Config->core['sign_in_attempts_block_count'] &&
		$User->get_sign_in_attempts_count() >= $Config->core['sign_in_attempts_block_count'] * 2 / 3
	) {
		$content	.= " $L->sign_in_attempts_left ".($Config->core['sign_in_attempts_block_count'] - $User->get_sign_in_attempts_count());
		sleep(1);
	} elseif (!$Config->core['sign_in_attempts_block_count'] && $User->get_sign_in_attempts_count() > 3) {
		sleep($User->get_sign_in_attempts_count() * 0.5);
	}
	$Page->error($content);
	unset($content);
}
