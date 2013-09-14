<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

/**
 * Requirements of server software versions for full-featured work:
 * * Apache Web Server	>= 2
 * * Nginx Web Server	>= 2
 *  * PHP				>= 5.4;
 *   Presence of PHP libraries:
 *   * mcrypt			>= 2.4
 *   * mbstring
 *   * cURL
 *  * MySQL				>= 5.0.7;
 */
if (version_compare(PHP_VERSION, '5.4', '<')) {
	exit('CleverStyle CMS require PHP 5.4 or higher');
}
/**
 * Time of start of execution, is used as current time
 */
define('MICROTIME',	microtime(true));	//Time in seconds (float)
define('TIME',		floor(MICROTIME));	//Time in seconds (integer)
define('OUT_CLEAN',	false);				//Enable output grabbing and cleaning (for security)
OUT_CLEAN && ob_start();
define('DIR',		__DIR__);			//Root directory
chdir(DIR);
define('CORE',		DIR.'/core');		//Core directory
require_once CORE.'/loader.php';		//Loader starting