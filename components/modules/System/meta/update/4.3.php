<?php
/**
 * @package   CleverStyle CMS
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

$Config = Config::instance();
$cdb    = DB::instance()->db_prime($Config->module('System')->db('users'));
$bots   = $cdb->q("SELECT `id` FROM `[prefix]users_groups` WHERE `group` = 3");
while ($bot = $cdb->fs($bots)) {
	$cdb->q(
		[
			"DELETE FROM `[prefix]users` WHERE `id` = $bot",
			"DELETE FROM `[prefix]users_groups` WHERE `id` = $bot"
		]
	);
}
$cdb->q(
	[
		"DELETE FROM `[prefix]groups` WHERE `id` = 3",
		"DELETE FROM `[prefix]groups_permissions` WHERE `id` = 3"
	]
);
$Cache = Cache::instance();
$Cache->clean();
