<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     System module
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;

$Config = Config::instance();
$core   = &$Config->core;
unset(
	$core['show_footer_info'],
	$core['footer_text']
);
Text::instance()->del($Config->module('System')->db('texts'), 'System/Config/core', 'footer_text');
$Config->save();
