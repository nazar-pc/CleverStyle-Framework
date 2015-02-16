<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
/**
 * This is custom loader that includes basic files and defines constants,
 * but do not call any class to leave that all for test cases, and unregisters shutdown function
 */
if (version_compare(PHP_VERSION, '5.4', '<')) {
	exit('CleverStyle CMS require PHP 5.4 or higher');
}
/**
 * Time of start of execution, is used as current time
 */
define('MICROTIME', microtime(true));                //Time in seconds (float)
define('TIME', floor(MICROTIME));                    //Time in seconds (integer)
define('DIR', realpath(__DIR__.'/../cscms.travis')); //Root directory
chdir(DIR);

require DIR.'/core/loader_base.php';     //Inclusion of loader base
require DIR.'/core/functions_global.php'; //Inclusion of functions that work with global state
/**
 * Wrapper around default `$_SERVER` superglobal
 */
$_SERVER = new _SERVER($_SERVER);
shutdown_function(false);
if (!defined('DEBUG')) {
	define('DEBUG', false);
}

if (!defined('DOMAIN')) {
	define('DOMAIN', 'cscms.travis');
}
/**
 * Will allow headers sending, and will output buffered content before exit anyway
 */
ob_start();
include __DIR__.'/Mock_object.php';
include __DIR__.'/Singleton.php';
