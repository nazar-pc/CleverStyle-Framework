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
$L								= Language::instance();
Index::instance()->title_auto	= false;
$rc								= &Config::instance()->route;
if (!isset($rc[0])) {
	return;
}
switch ($rc[0]) {
	case path($L->profile):
		$rc[0]	= 'profile';
}
if (!isset($rc[1])) {
	return;
}
switch ($rc[1]) {
	case path($L->settings):
		$rc[1]	= 'settings';
}
if (!isset($rc[2])) {
	return;
}
switch ($rc[2]) {
	case path($L->general):
		$rc[2]	= 'general';
	break;
	case path($L->change_password):
		$rc[2]	= 'change_password';
	break;
}
