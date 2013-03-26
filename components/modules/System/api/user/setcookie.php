<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $Page, $Key, $User, $db;
$rc = $Config->route;
if (
	!isset($rc[2]) ||
	!preg_match('/^[a-z0-9]{56}$/', $rc[2]) ||
	!($data = $Key->get($db->{$Config->module('System')->db('keys')}(), $rc[2], true))
) {
	$Page->json(0);
	return;
} else {
	$check = $data['check'];
	unset($data['check']);
	if (!($check == md5($User->ip.$User->forwarded_for.$User->client_ip.$User->user_agent._json_encode($data)))) {
		$Page->json(0);
		return;
	}
}
$Page->json((int)_setcookie($data['name'], $data['value'], $data['expire'], $data['httponly'], true));