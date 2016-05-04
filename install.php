<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Installer
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
if (version_compare(PHP_VERSION, '5.6', '<')) {
	echo 'CleverStyle CMS require PHP 5.6 or higher';
	return;
}
$cli = PHP_SAPI == 'cli';

require_once __DIR__.'/install/Installer.php';
require_once __DIR__.'/install/functions.php';
date_default_timezone_set('UTC');

if (PHP_SAPI == 'cli') {
	require __DIR__.'/install/cli.php';
} else {
	require __DIR__.'/install/web.php';
}
