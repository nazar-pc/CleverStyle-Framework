<?php
/**
 * @package		CleverStyle CMS
 * @version		$version$
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

/**
 * Minimal requirements for full-featured work:
 *	1) Versions of server software:
 *		* Apache Web Server				>= 2
 *		* PHP							>= 5.4;
 *			Presence of PHP libraries:
 *			* mcrypt					>= 2.4
 *			* iconv
 *			* mbstring
 *			* cURL
 *		* MySQL							>= 5.0.7;
 *	2) Browsers versions:
 *		* Opera Internet Browser		>= 11.10;
 *		* Microsoft Internet Explorer	>= 10;
 *		* Google Chrome					>= 11;
 *			(Webkit 534.24+)
 *		* Safari						>= 5;
 *			(Webkit 534.24+)
 *		* Mozilla Firefox				>= 4;
 */

/**
 * Time of start of execution, is used as current time
 */
define('MICROTIME',	microtime(true));	//Time in seconds (float)
define('TIME',		round(MICROTIME));	//Time in seconds (integer)
define('OUT_CLEAN',	false);				//Enable output grabbing and cleaning (for security)
OUT_CLEAN && ob_start();
define('DIR',		__DIR__);			//Root directory
chdir(DIR);
define('CORE',		DIR.'/core');		//Core directory
require_once CORE.'/loader.php';		//Loader starting