<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next events:<br>
 *  System/profile/info<code>
 *  [
 *   'id'	=> <i>user_id</i><br>
 *  ]</code>
 */
namespace	cs;
use
	h,
	cs\Page\Meta;
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
		'block_until'
	],
	$id
);
if ($data['status'] == User::STATUS_NOT_ACTIVATED) {
	error_code(404);
	$Page->error();
	return;
} elseif ($data['status'] == User::STATUS_INACTIVE) {
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
Meta::instance()
	->profile()
	->profile('username', $name)
	->image($User->avatar(256, $id));
$Page->content(
	h::{'div[layout][horizontal]'}(
		h::{'div.cs-profile-avatar img'}([
			'src'	=> $User->avatar(128, $id),
			'alt'	=> $name,
			'title'	=> $name
		]).
		h::{'div[flex]'}(
			h::h1(
				$L->profile_of_user($name)
			).
			h::{'cs-table[right-left] cs-table-row| cs-table-cell'}([
				$data['reg_date']
					? [
						h::h2("$L->reg_date:"),
						h::h2($L->to_locale(date($L->reg_date_format, $data['reg_date'])))
					]
					: false
			])
		)
	)
);
Event::instance()->fire(
	'System/profile/info',
	[
		'id'	=> $id
	]
);
