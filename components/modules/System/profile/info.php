<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  System/profile/info<code>
 *  [
 *   'id'	=> <i>user_id</i><br>
 *  ]</code>
 */
namespace	cs;
use			h;
$L		= Language::instance();
$Page	= Page::instance();
$User	= User::instance();
$rc		= Config::instance()->route;
if (!isset($rc[1], $rc[2]) || !($id = $User->get_id(hash('sha224', $rc[2])))) {
	error_code(404);
	$Page->error();
	return;
}
$data	= $User->get(
	[
		'username',
		'login',
		'reg_date',
		'status',
		'block_until',
		'avatar'
	],
	$id
);
if ($data['status'] == -1) {
	error_code(404);
	$Page->error();
	return;
} elseif ($data['status'] == 0) {
	$Page->warning(
		h::tr([
			$L->account_disabled
		])
	);
	return;
} elseif ($data['block_until'] > TIME) {
	$Page->warning(
		h::tr([
			$L->account_temporarily_blocked
		])
	);
}
$name	= $data['username'] ? $data['username'].($data['username'] != $data['login'] ? ' aka '.$data['login'] : '') : $data['login'];
$Page->title($L->profile_of_user($name));
$Page->og(
	'type',
	'profile'
)->og(
	'username',
	$name,
	'profile:'
);
$Page->content(
	h::{'table.cs-table-borderless.cs-profile-table tr'}([
		h::{'td.cs-profile-avatar[rowspan=2] img'}([
			'src'	=> $User->avatar(128, $id),
			'alt'	=> $name,
			'title'	=> $name
		]).
		h::{'td h1'}(
			$L->profile_of_user($name)
		),

		h::{'td table.cs-right-odd.cs-left-even tr'}([
			($data['reg_date'] ? h::td([
				h::h2($L->reg_date.':'),
				h::h2($L->to_locale(date($L->reg_date_format, $data['reg_date'])))
			])  : false)
		])
	])
);
Trigger::instance()->run(
	'System/profile/info',
	[
		'id'	=> $id
	]
);