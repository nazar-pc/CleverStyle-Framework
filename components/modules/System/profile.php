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
$rc			= &Config::instance()->route;
$subparts	= file_get_json(MFOLDER.'/index.json')[$rc[0]];
$User		= User::instance();
if (
	(
		!isset($rc[1]) && $User->user()
	) ||
	(
		isset($rc[1]) && !in_array($rc[1], $subparts)
	)
) {
	if (isset($rc[1])) {
		$rc[2]	= $rc[1];
	} else {
		$rc[2]	= $User->login;
	}
	$rc[1]	= $subparts[0];
}
