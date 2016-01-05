<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
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
 * * PHP 5.5+
 *  PHP libraries MUST be present:
 *  * cURL
 *  Optional PHP libraries:
 *  * APCu, Memcached
 * * or HHVM 3.3.2+ LTS or HHVM 3.4.1+
 * * MySQL 5.6+
 * * or MariaDB 10.0.5+
 */
if (version_compare(PHP_VERSION, '5.5', '<')) {
	echo 'CleverStyle CMS require PHP 5.5 or higher';
	return;
}
/**
 * Time of start of execution, is used as current time
 */
define('MICROTIME', microtime(true)); //Time in seconds (float)
define('TIME', floor(MICROTIME));     //Time in seconds (integer)
define('DIR', __DIR__);               //Root directory
chdir(DIR);
require_once DIR.'/core/loader.php';  //Loader starting
