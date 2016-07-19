<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Time of start of execution, is used as current time
 */
define('MICROTIME', microtime(true)); //In seconds (float)
define('TIME', floor(MICROTIME));     //In seconds (integer)
//Root directory
defined('DIR') || define('DIR', realpath(__DIR__.'/..'));
chdir(DIR);
/**
 * Defining of basic constants with paths to system directories
 */
defined('LANGUAGES') || define('LANGUAGES', DIR.'/core/languages');
defined('CUSTOM') || define('CUSTOM', DIR.'/custom');
defined('BLOCKS') || define('BLOCKS', DIR.'/blocks');
defined('MODULES') || define('MODULES', DIR.'/modules');
defined('STORAGE') || define('STORAGE', DIR.'/storage');
defined('PUBLIC_STORAGE') || define('PUBLIC_STORAGE', STORAGE.'/public');
defined('CACHE') || define('CACHE', STORAGE.'/cache');
defined('LOGS') || define('LOGS', STORAGE.'/logs');
defined('TEMP') || define('TEMP', STORAGE.'/temp');
defined('PUBLIC_CACHE') || define('PUBLIC_CACHE', STORAGE.'/pcache');
defined('THEMES') || define('THEMES', DIR.'/themes');
/**
 * Useful PHP Functions
 */
require_once DIR.'/core/thirdparty/upf.php';
/**
 * `vendor/autoload.php` might be created or might now be there - include if only present
 */
file_exists(DIR.'/vendor/autoload.php') && require_once DIR.'/vendor/autoload.php';
/**
 * Common system functions and system-specific autoloader
 */
require_once DIR.'/core/functions.php';

error_reporting(E_ALL);

/**
 * Request file stream wrappers that is used by system when handling uploaded files
 */
stream_wrapper_register('request-file', cs\Request\File_stream::class);
/**
 * Hack: HHVM doesn't have ENT_DISALLOWED constant unfortunately, remove when https://github.com/facebook/hhvm/issues/4938 resolved
 */
defined('ENT_DISALLOWED') || define('ENT_DISALLOWED', 128);
if (!is_dir(CACHE)) {
	/** @noinspection MkdirRaceConditionInspection */
	mkdir(CACHE, 0770);
}
if (!is_dir(LOGS)) {
	/** @noinspection MkdirRaceConditionInspection */
	mkdir(LOGS, 0770);
}
/**
 * Including of custom files
 */
foreach (glob(CUSTOM.'/*.php') ?: [] as $custom) {
	require_once $custom;
}

date_default_timezone_set(cs\Core::instance()->timezone);
