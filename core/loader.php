<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
require CORE.'/upf.php';							//Inclusion of UPF
require CORE.'/functions.php';						//Inclusion of general system functions
_require_once(DIR.'/vendor/autoload.php', false);	//Inclusion of composer's autoloader.php if exists

global $timeload, $loader_init_memory;

$timeload['start']			= MICROTIME;

error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
header('Vary: Content-Language,User-Agent,Cookie');
mb_internal_encoding('utf-8');
header('Connection: close');

/**
 * Setting of basic constants with paths to system directories
 */
/**
 * Directory for configuration
 */
define('CONFIG',	DIR.'/config');
/**
 * Directory for main core classes
 */
define('CLASSES',	CORE.'/classes');
/**
 * Directory for main core traits
 */
define('TRAITS',	CORE.'/traits');
/**
 * Directory for cache, DB, storage and translation engines
 */
define('ENGINES',	CORE.'/engines');
/**
 * Languages directory
 */
define('LANGUAGES',	CORE.'/languages');
/**
 * Directory for CSS files
 */
define('CSS',		DIR.'/includes/css');
/**
 * Directory for images
 */
define('IMG',		DIR.'/includes/img');
/**
 * Directory for JavaScript files
 */
define('JS',		DIR.'/includes/js');
/**
 * Templates directory
 */
define('TEMPLATES',	DIR.'/templates');
/**
 * Blocks directory
 */
define('BLOCKS',	DIR.'/components/blocks');
/**
 * Modules directory
 */
define('MODULES',	DIR.'/components/modules');
/**
 * Plugins directory
 */
define('PLUGINS',	DIR.'/components/plugins');
/**
 * Local public storage for current domain
 */
define('STORAGE',	DIR.'/storage/public');
/**
 * Cache directory for current domain
 */
define('CACHE',		DIR.'/storage/cache');
/**
 * Log directory for current domain
 */
define('LOGS',		DIR.'/storage/logs');
/**
 * Temp directory for current domain
 */
define('TEMP',		DIR.'/storage/temp');
/**
 * Directory with public cache (available from the outside)
 */
define('PCACHE',	DIR.'/storage/pcache');
/**
 * Themes dir
 */
define('THEMES',	DIR.'/themes');

/**
 * Load information about minimal needed Software versions
 */
require_once CORE.'/required_verions.php';
/**
 * Including of custom user file
 */
_include_once(DIR.'/custom.php', false);

$timeload['loader_init']	= microtime(true);
$loader_init_memory			= memory_get_usage();CONFIG;
/**
 * System starting
 */
Core::instance();
defined('CS_ERROR_HANDLER') && CS_ERROR_HANDLER && Error::instance();
Index::instance()->__finish();
Page::instance()->__finish();
User::instance()->__finish();