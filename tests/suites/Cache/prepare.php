<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2011—2013
 * @license		MIT License, see license.txt
 */
require DIR.'/core/classes/Cache.php';
require DIR.'/core/engines/Cache/_Abstract.php';
require DIR.'/core/engines/Cache/FileSystem.php';
require DIR.'/core/classes/Core.php';
require __DIR__.'/stubs/Core.php';
define('CACHE', TEMP);