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
$result	= $Cache->set('test', 5);
$Cache->disable();
$result	= $result && $Cache->cache_state() === false;
$result	= $result && $Cache->test === false;
return $result ? 0 : 'Failed';