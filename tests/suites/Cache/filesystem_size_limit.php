<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2011—2013
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
if (!$Cache->set('test', 5)) {
	return '::set() failed';
}
if (!$Cache->set('test', '111')) {
	return 'second ::set() method does not work';
}
return $Cache->set('test', '111111') === false ? 0 : 'Size limit does not works';