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
$User	= User::instance();
if ($User->guest()) {
	Page::instance()->json(1);
	return;
}
if (isset($_POST['sign_out'])) {
	$User->del_session();
	/**
	 * Hack for 403 after sign out in administration
	 */
	_setcookie('sign_out', 1, TIME + 5, true);
	Page::instance()->json(1);
}
