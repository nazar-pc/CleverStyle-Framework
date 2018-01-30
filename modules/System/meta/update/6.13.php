<?php
/**
 * @package  CleverStyle Framework
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs;

file_put_contents(
	DIR.'/config/main.json',
	str_replace(
		['"storage_type"	'],
		['"storage_driver"'],
		file_get_contents(DIR.'/config/main.json')
	)
);

$Config = Config::instance();
foreach ($Config->storage as &$storage) {
	if (@$storage['connection']) {
		$storage['driver'] = $storage['connection'];
	}
}
$Config->save();
