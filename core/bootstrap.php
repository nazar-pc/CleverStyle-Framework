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
chdir(__DIR__.'/..');
define('DIR', getcwd());               //Root directory
/**
 * Defining of basic constants with paths to system directories
 */
define('ENGINES', DIR.'/core/engines');
define('LANGUAGES', DIR.'/core/languages');
define('CUSTOM', DIR.'/custom');
define('TEMPLATES', DIR.'/templates');
define('BLOCKS', DIR.'/components/blocks');
define('MODULES', DIR.'/components/modules');
define('PLUGINS', DIR.'/components/plugins');
define('STORAGE', DIR.'/storage');
define('PUBLIC_STORAGE', STORAGE.'/public');
define('CACHE', STORAGE.'/cache');
define('LOGS', STORAGE.'/logs');
define('TEMP', STORAGE.'/temp');
define('PUBLIC_CACHE', STORAGE.'/pcache');
define('THEMES', DIR.'/themes');
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
 * Stream wrapper for PSR7 interface
 */
stream_wrapper_register('request-psr7-data', cs\Request\Psr7_data_stream::class);
/**
 * Hack: HHVM doesn't have ENT_DISALLOWED constant unfortunately, remove when https://github.com/facebook/hhvm/issues/4938 resolved
 */
defined('ENT_DISALLOWED') || define('ENT_DISALLOWED', 128);

if (!is_dir(PUBLIC_STORAGE)) {
	/** @noinspection MkdirRaceConditionInspection */
	@mkdir(PUBLIC_STORAGE, 0775, true);
	file_put_contents(
		PUBLIC_STORAGE.'/.htaccess',
		/** @lang ApacheConfig */
		<<<HTACCESS
Allow From All
<ifModule mod_headers.c>
	Header always append X-Frame-Options DENY
	Header set Content-Type application/octet-stream
</ifModule>

HTACCESS
	);
}
if (!is_dir(CACHE)) {
	/** @noinspection MkdirRaceConditionInspection */
	@mkdir(CACHE, 0770);
}
if (!is_dir(PUBLIC_CACHE)) {
	/** @noinspection MkdirRaceConditionInspection */
	@mkdir(PUBLIC_CACHE, 0770);
	file_put_contents(
		PUBLIC_CACHE.'/.htaccess',
		/** @lang ApacheConfig */
		<<<HTACCESS
<FilesMatch "\.(css|js|html)$">
	Allow From All
</FilesMatch>
<ifModule mod_expires.c>
	ExpiresActive On
	ExpiresDefault "access plus 1 month"
</ifModule>
<ifModule mod_headers.c>
	Header set Cache-Control "max-age=2592000, public"
</ifModule>
AddEncoding gzip .js
AddEncoding gzip .css
AddEncoding gzip .html

HTACCESS
	);
}
if (!is_dir(LOGS)) {
	/** @noinspection MkdirRaceConditionInspection */
	@mkdir(LOGS, 0770);
}
if (!is_dir(TEMP)) {
	/** @noinspection MkdirRaceConditionInspection */
	@mkdir(TEMP, 0775);
	file_put_contents(
		TEMP.'/.htaccess',
		/** @lang ApacheConfig */
		<<<HTACCESS
Allow From All

HTACCESS
	);
}
/**
 * Including of custom files
 */
foreach (glob(CUSTOM.'/*.php') ?: [] as $custom) {
	require_once $custom;
}

date_default_timezone_set(cs\Core::instance()->timezone);
