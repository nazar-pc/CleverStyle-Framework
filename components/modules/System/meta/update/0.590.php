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
$Config = Config::instance();
$core   = &$Config->core;
unset(
	$core['show_db_queries'],
	$core['show_cookies']
);
$Config->save();
