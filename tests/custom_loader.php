<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Test
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
/**
 * This is custom loader that includes basic files and defines constants and define Singleton which is not a Singleton at all (return new instance every time),
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
define('CORE',		DIR.'/core');							//Core directory

/**
 * Directory for thirdparty libraries
 */
define('THIRDPARTY', CORE.'/thirdparty');
/**
 * Fallback for PHP 5.5 hashing functions, that are not present in PHP 5.4
 */
if (!defined('PASSWORD_DEFAULT')) {
	require THIRDPARTY.'/password_compat.php';
}
require THIRDPARTY.'/upf.php';                    //Inclusion of Useful PHP Functions
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
shutdown_function(true);
trait Singleton {
	final protected function __construct () {}
	protected function construct () {}
	static function instance ($check = false) {
		static $instance;
		if ($check) {
			return isset($instance) ? $instance : False_class::instance();
		}
		$class	= ltrim(get_called_class(), '\\');
		if (substr($class, 0, 2) == 'cs' && class_exists('cs\\custom'.substr($class, 2), false)) {
			$instance	= 'cs\\custom'.substr($class, 2);
			$instance	= $instance::instance();
		} else {
			$instance	= new static;
		}
		$instance->construct();
		return $instance;
	}
	final protected function __clone () {}
	final protected function __wakeup() {}
}
if (!defined('DOMAIN')) {
	define('DOMAIN', 'cscms.travis');
}
