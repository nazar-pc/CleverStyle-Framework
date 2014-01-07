<?php
/**
 * @package		CleverStyle CMS
 *
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
use	nazarpc\CSTester;
if (version_compare(PHP_VERSION, '5.4', '<')) {
	exit('CleverStyle CMS require PHP 5.4 or higher');
}
require __DIR__.'/core/classes/thirdparty/nazarpc/CSTester.php';
(new CSTester(__DIR__.'/tests'))->run();
