<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Config		= Config::instance();
$texts_db	= DB::instance()->db_prime(
	$Config->module('System')->db('texts')
);
$texts		= $texts_db->q(
	"SELECT `id`, `text`, `lang`
	FROM `[prefix]texts_data`"
);
time_limit_pause();
while ($text	= $texts_db->f($texts)) {
	$texts_db->q(
		"UPDATE `[prefix]texts_data`
		SET `text_md5` = '%s'
		WHERE
			`id`	= '%s' AND
			`lang`	= '%s'",
		md5($text['text']),
		$text['id'],
		$text['lang']
	);
}
time_limit_pause(false);
