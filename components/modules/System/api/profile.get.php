<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$User	= User::instance();
if ($User->guest()) {
	define('ERROR_CODE', 403);
	return;
}
$fields	= [
	'id',
	'login',
	'username',
	'language',
	'timezone',
	'gender',
	'birthday',
	'avatar',
	'website',
	'skype',
	'about'
];
$Page	= Page::instance();
$id		= $User->id;
$Page->json($User->get($fields, $id));