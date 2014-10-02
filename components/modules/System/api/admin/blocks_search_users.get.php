<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
use h;
$L				= Language::instance();
$Page			= Page::instance();
$User			= User::instance();
$users_list		= $User->search_users($_GET['search_phrase']);
$found_users	= explode(',', $_GET['found_users']);
$permission		= (int)$_GET['permission'];
$content		= [];
foreach ($users_list as $user) {
	if (in_array($user, $found_users)) {
		continue;
	}
	$found_users[]	= $user;
	$value			= $User->db()->qfs([
		"SELECT `value`
		FROM `[prefix]users_permissions`
		WHERE
			`id`			= '%s' AND
			`permission`	= '%s'",
		$user,
		$permission
	]);
	$content[]		= h::th($User->username($user)).
		h::{'td input[type=radio]'}([
			'name'		=> 'users['.$user.']',
			'checked'	=> $value !== false ? $value : -1,
			'value'		=> [-1, 0, 1],
			'in'		=> [
				$L->inherited.' ('.($value !== false && !$value ? '-' : '+').')',
				$L->deny,
				$L->allow
			]
		]);
}
$Page->json(
	h::{'table.cs-table-borderless.cs-center-all tr'}($content)
);
