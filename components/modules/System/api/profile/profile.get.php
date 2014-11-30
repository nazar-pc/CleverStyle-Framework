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
$User	= User::instance();
if ($User->guest()) {
	error_code(403);
	return;
}
$fields	= [
	'id',
	'login',
	'username',
	'language',
	'timezone',
	'avatar'
];
$Page	= Page::instance();
$id		= $User->id;
$Page->json($User->get($fields, $id));
