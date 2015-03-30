<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Requirements:
 * * Apache2
 *  Apache2 modules MUST be enabled:
 *  * rewrite
 *  * headers
 *  Optional Apache2 modules:
 *  * expires
 * * or Nginx
 * * PHP 5.4+
 *  PHP libraries MUST be present:
 *  * Mcrypt
 *  * cURL
 *  Optional PHP libraries:
 *  * APCu, Memcached
 * * or HHVM 3.3.2+ LTS or HHVM 3.4.1+
 * * MySQL 5.5+
 * * or MariaDB 5.5+
 */
if (version_compare(PHP_VERSION, '5.4', '<')) {
	echo 'CleverStyle CMS require PHP 5.4 or higher';
	return;
}
if (version_compare(PHP_VERSION, '5.5', '<')) {
	trigger_error(
		'CleverStyle CMS 2.0 will require PHP 5.5 or higher, please update your PHP version in order to have smooth process of CleverStyle CMS updating  in future',
		E_USER_DEPRECATED
	);
}
/**
 * Time of start of execution, is used as current time
 */
define('MICROTIME', microtime(true)); //Time in seconds (float)
define('TIME', floor(MICROTIME));     //Time in seconds (integer)
define('DIR', __DIR__);               //Root directory
chdir(DIR);
require_once DIR.'/core/loader.php';  //Loader starting
