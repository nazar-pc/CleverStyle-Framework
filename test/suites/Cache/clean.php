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
 * @var Cache $Cache
 */
$Cache	= Cache::instance();
$value	= uniqid('cache', true);
$result	= $Cache->set('test', $value);
$result	= $result && $Cache->get('test') === $value;
$result	= $result && $Cache->clean();
$result	= $result && $Cache->get('test') === false;
return $result ? 0 : 'Failed';