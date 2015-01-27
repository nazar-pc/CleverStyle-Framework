<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Test
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
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
define('MICROTIME',	microtime(true));						//Time in seconds (float)
define('TIME',		floor(MICROTIME));						//Time in seconds (integer)
define('DIR',		realpath(__DIR__.'/../cscms.travis'));	//Root directory
chdir(DIR);

/**
 * Fallback for PHP 5.5 hashing functions, that are not present in PHP 5.4
 */
if (!defined('PASSWORD_DEFAULT')) {
	require DIR.'/core/thirdparty/password_compat.php';
}
require DIR.'/core/thirdparty/upf.php';           //Inclusion of Useful PHP Functions
_require_once(DIR.'/vendor/autoload.php', false); //Inclusion of composer's autoloader.php with user's dependencies
require DIR.'/core/functions.php';                //Inclusion of general system functions and system autoloader

error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
header('Vary: Content-Language,User-Agent,Cookie');
mb_internal_encoding('utf-8');
header('Connection: close');
/**
 * Defining of basic constants with paths to system directories
 */
/**
 * Directory for cache, DB and storage engines
 */
define('ENGINES', DIR.'/core/engines');
/**
 * Languages directory
 */
define('LANGUAGES', DIR.'/core/languages');
/**
 * Languages directory
 */
define('CUSTOM', DIR.'/custom');
/**
 * Templates directory
 */
define('TEMPLATES', DIR.'/templates');
/**
 * Blocks directory
 */
define('BLOCKS', DIR.'/components/blocks');
/**
 * Modules directory
 */
define('MODULES', DIR.'/components/modules');
/**
 * Plugins directory
 */
define('PLUGINS', DIR.'/components/plugins');
/**
 * Local public storage for current domain
 */
define('STORAGE', DIR.'/storage/public');
/**
 * Cache directory for current domain
 */
define('CACHE', DIR.'/storage/cache');
/**
 * Log directory for current domain
 */
define('LOGS', DIR.'/storage/logs');
/**
 * Temp directory for current domain
 */
define('TEMP', DIR.'/storage/temp');
/**
 * Directory with public cache (available from the outside)
 */
define('PCACHE', DIR.'/storage/pcache');
/**
 * Themes dir
 */
define('THEMES', DIR.'/themes');
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
