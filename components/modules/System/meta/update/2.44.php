<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
$Config = Config::instance();
$db     = DB::instance();
foreach ($Config->db as $db_index => $db_params) {
	$db_name = $db_index == 0 ? Core::instance()->db_name : $db_params['name'];
	$db->db_prime($db_index)->q(
		"ALTER DATABASE `$db_name` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci"
	);
}
$db->db_prime(0)->q(
	[
		'ALTER TABLE `[prefix]config` DROP PRIMARY KEY, ADD PRIMARY KEY (`domain`(191))',
		'ALTER TABLE `[prefix]config` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
		'REPAIR TABLE `[prefix]users_permissions`',
		'OPTIMIZE TABLE `[prefix]users_permissions`'

	]
);
