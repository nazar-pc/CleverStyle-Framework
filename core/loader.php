<?php
/**
 * @package        CleverStyle CMS
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
/**
 * Fallback for PHP 5.5 hashing functions, that are not present in PHP 5.4
 */
if (!defined('PASSWORD_DEFAULT')) {
	require __DIR__.'/thirdparty/password_compat.php';
}
require CORE.'/thirdparty/upf.php';               //Inclusion of Useful PHP Functions
_require_once(DIR.'/vendor/autoload.php', false); //Inclusion of composer's autoloader.php with user's dependencies
require CORE.'/functions.php';                    //Inclusion of general system functions and system autoloader

error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
header('Vary: Content-Language,User-Agent,Cookie');
mb_internal_encoding('utf-8');
header('Connection: close');
/**
 * Defining of basic constants with paths to system directories
 */
/**
 * Directory for configuration
 */
define('CONFIG', DIR.'/config');
/**
 * Directory for main core classes
 */
define('CLASSES', CORE.'/classes');
/**
 * Directory for thirdparty libraries
 */
define('THIRDPARTY', CORE.'/thirdparty');
/**
 * Directory for main core traits
 */
define('TRAITS', CORE.'/traits');
/**
 * Directory for cache, DB and storage engines
 */
define('ENGINES', CORE.'/engines');
/**
 * Languages directory
 */
define('LANGUAGES', CORE.'/languages');
/**
 * Languages directory
 */
define('CUSTOM', DIR.'/custom');
/**
 * Directory for CSS files
 */
define('CSS', DIR.'/includes/css');
/**
 * Directory for images
 */
define('IMG', DIR.'/includes/img');
/**
 * Directory for JavaScript files
 */
define('JS', DIR.'/includes/js');
/**
 * Directory for Web Components files
 */
define('HTML', DIR.'/includes/html');
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
 * Including of custom user files
 */
foreach (glob(CUSTOM.'/*.php') ?: [] as $custom) {
	include $custom;
}
unset($custom);
/**
 * System running
 */
Core::instance();
Language::instance();
Index::instance();
