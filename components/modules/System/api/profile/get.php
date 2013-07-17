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
if (isset($_POST['id'])) {
	$Page->json(array_map(
		function ($id) use ($fields, $User) {
			return $User->get($fields, $id);
		},
		array_intersect(
			_int((array)$_POST['id']) ?: [],
			$User->get_contacts()
		)
	));
} else {
	$Config	= Config::instance();
	$id		= $User->id;
	if (isset($Config->route[2])) {
		$id	= (int)$Config->route[2];
		if (!in_array($id, $User->get_contacts())) {
			define('ERROR_CODE', 403);
			$Page->error('User is not in your contacts');
			return;
		}
	}
	$Page->json($User->get($fields, $id));
}