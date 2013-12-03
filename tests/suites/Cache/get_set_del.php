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
 * @var Cache $Cache
 */
$Cache	= Cache::instance();
$value	= uniqid('cache', true);
if (!$Cache->set('test', $value)) {
	return '::set() failed';
}
if (!$Cache->get('test') === $value) {
	return '::get() failed';
}
if (!$Cache->del('test')) {
	return '::del() failed';
}
return $Cache->get('test') === false ? 0 : 'Value still exists';