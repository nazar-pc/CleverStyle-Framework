<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Core	= Core::instance();
if (!$Core) {
	return 'Instance creation failed';
}
if (
	$Core->domain	!= 'example.com' ||
	$Core->db_name	!= 'CleverStyle_db' ||
	$Core->key		!= '11111111111111111111111111111111111111111111111111111111'
) {
	return 'Failed load main configuration';
}
return 0;
