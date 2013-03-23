<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
if (isset($_POST['logout'])) {
	global $User;
	$User->del_session();
	var_dump(_setcookie('logout', '1', 0, true, true));
}