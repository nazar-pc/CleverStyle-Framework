<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;

$Config = Config::instance();
$core   = &$Config->core;
unset(
	$core['gzip_compression']
);
$Config->save();
