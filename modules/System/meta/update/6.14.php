<?php
/**
 * @package   CleverStyle Framework
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

file_put_contents(
	DIR.'/config/main.json',
	str_replace(
		['"db_type"'],
		['"db_driver"'],
		file_get_contents(DIR.'/config/main.json')
	)
);

$Config = Config::instance();
foreach ($Config->db as &$db) {
	if (@$db['type']) {
		$db['driver'] = $db['type'];
	}
	if (@$db['mirrors']) {
		foreach ($db['mirrors'] as &$mirror) {
			$mirror['driver'] = $mirror['type'];
		}
		unset($mirror);
	}
}
$Config->save();
