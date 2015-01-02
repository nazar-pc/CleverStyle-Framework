<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
if (!isset($_GET['email'])) {
	error_code(400);
	return;
}
if (!Mail::instance()->send_to($_GET['email'], 'Email testing on '.Config::instance()->core['name'], 'Test email')) {
	error_code(500);
}
