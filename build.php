<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Builder
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
if (!Phar::canWrite()) {
	echo "CleverStyle CMS Builder can't work, set, please, \"phar.readonly=off\" option in \"php.ini\"";
	return;
}
define('DIR', __DIR__);
require_once DIR.'/build/Builder.php';
require_once DIR.'/core/thirdparty/nazarpc/BananaHTML.php';
require_once DIR.'/core/classes/h/Base.php';
require_once DIR.'/core/classes/h.php';
require_once DIR.'/core/thirdparty/upf.php';
require_once DIR.'/core/functions.php';
date_default_timezone_set('UTC');

if (PHP_SAPI == 'cli') {
	require __DIR__.'/build/cli.php';
} else {
	require __DIR__.'/build/web.php';
}
