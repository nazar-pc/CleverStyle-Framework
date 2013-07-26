<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
define('DEBUG', false);
/**
 * Cache size limit to 5 bytes
 */
Core::instance()->cache_size = 5 / 1024 / 1024;
/**
 * @var Cache $Cache
 */
$Cache	= Cache::instance();
$result	= $Cache->set('test', 5);
$result	= $result && $Cache->set('test', '111');
$result	= $result && $Cache->set('test', '111111') === false;
return $result ? 0 : 'Failed';