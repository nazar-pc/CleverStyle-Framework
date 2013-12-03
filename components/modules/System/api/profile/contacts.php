<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2011—2013
 * @license		MIT License, see license.txt
 */
namespace	cs;
$User	= User::instance();
if ($User->guest()) {
	error_code(403);
	return;
}
Page::instance()->json($User->get_contacts());