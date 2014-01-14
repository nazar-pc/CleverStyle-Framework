<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
define('DEBUG', false);
/**
 * @var Cache $Cache
 */
$Cache	= Cache::instance();
if (!$Cache->set('test', 5)) {
	return '::set() failed';
}
$Cache->disable();
if ($Cache->cache_state() !== false) {
	return '::disable() method does not work';
}
return $Cache->test === false ? 0 : 'Value still exists';
