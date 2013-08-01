<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
define('DEBUG', true);
/**
 * @var Cache $Cache
 */
$Cache	= Cache::instance();
if (!$Cache->set('test', 5)) {
	return '::set() failed';
}
if ($Cache->cache_state() !== false) {
	return '::cache_state() failed';
}
return $Cache->test === false ? 0 : 'Value still exists';