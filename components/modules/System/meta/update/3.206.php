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
if ($Config->core['language'] == 'Українська') {
	$Config->core['language'] = 'Ukrainian';
}
if ($Config->core['language'] == 'Русский') {
	$Config->core['language'] = 'Russian';
}
foreach ($Config->core['active_languages'] as &$language) {
	if ($language == 'Українська') {
		$language = 'Ukrainian';
	}
	if ($language == 'Русский') {
		$language = 'Russian';
	}
}
$Config->save();
DB::instance()->db_prime($Config->module('System')->db('users'))->q(
	"UPDATE `[prefix]users`
	SET `language` = 'Ukrainian'
	WHERE `language` = 'Українська'"
);
DB::instance()->db_prime($Config->module('System')->db('users'))->q(
	"UPDATE `[prefix]users`
	SET `language` = 'Russian'
	WHERE `language` = 'Русский'"
);
$Cache = Cache::instance();
$Cache->del('languages');
$Cache->del('users');
