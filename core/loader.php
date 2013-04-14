<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
require CORE.'/upf.php';						//Including UPF
require CORE.'/functions.php';					//Including general system functions

global $Core, $timeload, $loader_init_memory, $interface;

$timeload['start']			= MICROTIME;
$interface					= true;

error_reporting(E_ALL);
//error_reporting(0);

header('Content-Type: text/html; charset=utf-8');
header('Vary: Content-Language,User-Agent,Cookie');
mb_internal_encoding('utf-8');
header('Connection: close');

/**
 * Setting of basic constants with paths to system directories
 */
define('CONFIG',	DIR.'/config');					//Directory for configuration
define('CLASSES',	CORE.'/classes');				//Directory for main core classes
define('ENGINES',	CORE.'/engines');				//Directory for cache, DB, storage and translation engines
define('LANGUAGES',	CORE.'/languages');				//Languages directory
define('CSS',		DIR.'/includes/css');			//Directory with CSS files
define('IMG',		DIR.'/includes/img');			//Directory with images
define('JS',		DIR.'/includes/js');			//Directory for JavaScript files
define('TEMPLATES',	DIR.'/templates');				//Templates directory
define('BLOCKS',	DIR.'/components/blocks');		//Blocks directory
define('MODULES',	DIR.'/components/modules');		//Modules directory
define('PLUGINS',	DIR.'/components/plugins');		//Plugins directory
define('STORAGE',	DIR.'/storage/public');			//Local public storage for current domain
define('CACHE',		DIR.'/storage/cache');			//Cache directory for current domain
define('LOGS',		DIR.'/storage/logs');			//Log directory for current domain
define('TEMP',		DIR.'/storage/temp');			//Temp directory for current domain
define('PCACHE',	DIR.'/storage/pcache');			//Directory with public cache (available from the outside)
define('THEMES',	DIR.'/themes');					//themes dir

/**
 * Load information about minimal needed Software versions
 */
require_once CORE.'/required_verions.php';
/**
 * Including of custom user file
 */
_include_once(DIR.'/custom.php', false);

$timeload['loader_init']	= microtime(true);
$loader_init_memory			= memory_get_usage();

/**
 * Loading of core and primary system classes, creating of necessary objects.
 * WARNING: Disabling of creating the following objects or changing the order almost 100% will lead to a complete engine inoperable!
 * If necessary, change the logic of the primary objects of engine, use custom.php file for including own versions of classes,
 * and this versions will be used instead of system ones.
 *
 * Core object for loading of system configuration, creating of global objects, encryption, API requests sending, and triggers processing.
 */
$Core						= new \cs\Core;

$Core->create([
	defined('CS_ERROR_HANDLER') && CS_ERROR_HANDLER ? 'cs\\Error' : false,	//Object of errors processing
	'cs\\Cache',															//System cache object
	'cs\\Text',																//Object of multilingual content
	['cs\\Language',	'L'],												//Object of multilingual interface
	'cs\\Page',																//Page generation object
	['cs\\DB',			'db'],												//DataBase object
	'_cs\\Storage',															//Storage object
	'cs\\Config',															//Configuration object
	'_cs\\Mail',															//Object for sending of emails
	'_cs\\Key',																//Objects of temporary keys
	'cs\\User',																//Object of user
	'cs\\Index'																//Object, that supports of components processing
]);
$Core->__finish();															//Destroying of objects, displaying of generated content and correct termination