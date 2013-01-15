<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $Page, $Key, $User, $db;
$rc = $Config->route;
if (
	!isset($rc[3]) ||
	!preg_match('/^[a-z0-9]{56}$/', $rc[3]) ||
	!($data = $Key->get($db->{$Config->module('System')->db('keys')}(), $rc[3], true))
) {
	$Page->content(0);
	return;
} else {
	$check = $data['check'];
	unset($data['check']);
	if (!($check == md5($User->ip.$User->forwarded_for.$User->client_ip.$User->user_agent._json_encode($data)))) {
		$Page->content(0);
		return;
	}
}
$Page->content((int)_setcookie($data['name'], $data['value'], $data['expire'], $data['httponly'], true));