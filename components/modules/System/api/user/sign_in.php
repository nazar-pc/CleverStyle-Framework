<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Config	= Config::instance();
$L		= Language::instance();
$Page	= Page::instance();
$User	= User::instance();
if (!$User->guest()) {
	return;
} elseif ($Config->core['sign_in_attempts_block_count'] && $User->get_sign_in_attempts_count() >= $Config->core['sign_in_attempts_block_count']) {
	$User->sign_in_result(false);
	error_code(403);
	$Page->error("$L->sign_in_attempts_ends_try_after ".format_time($Config->core['sign_in_attempts_block_time']));
	return;
}
$id = $User->get_id(@$_POST['login']);
if (
	$id &&
	$User->validate_password(@$_POST['password'], $id, true)
) {
	if ($User->get('status', $id) == User::STATUS_NOT_ACTIVATED) {
		error_code(403);
		$Page->error($L->your_account_is_not_active);
		return;
	} elseif ($User->get('status', $id) == User::STATUS_INACTIVE) {
		error_code(403);
		$Page->error($L->your_account_disabled);
		return;
	}
	$User->add_session($id);
	$User->sign_in_result(true);
} else {
	$User->sign_in_result(false);
	error_code(400);
	$content	= $L->auth_error_sign_in;
	if (
		$Config->core['sign_in_attempts_block_count'] &&
		$User->get_sign_in_attempts_count() >= $Config->core['sign_in_attempts_block_count'] * 2 / 3
	) {
		$content	.= " $L->sign_in_attempts_left ".($Config->core['sign_in_attempts_block_count'] - $User->get_sign_in_attempts_count());
	}
	$Page->error($content);
	unset($content);
}
