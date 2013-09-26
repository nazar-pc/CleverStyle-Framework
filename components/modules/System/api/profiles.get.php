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
	error_code(403);
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
$Config	= Config::instance();
if (isset($Config->route[1])) {
	$id		= _int(explode(',', $Config->route[1]));
	$single	= count($id) == 1;
	if (
		!$User->admin() &&
		!(
			$id = array_intersect($id, $User->get_contacts())
		)
	) {
		error_code(403);
		$Page->error('User is not in your contacts');
	}
	if ($single) {
		$Page->json($User->get($fields, $id));
	} else {
		$Page->json(array_map(
			function ($id) use ($fields, $User) {
				return $User->get($fields, $id);
			},
			$id
		));
	}
} else {
	error_code(400);
	$Page->error('Specified ids are expected');
}