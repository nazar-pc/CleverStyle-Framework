<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
/**
 * This is custom loader that includes basic files and defines constants,
 * but do not call any class to leave that all for test cases
 */
if (!defined('MICROTIME')) {
	/**
	 * Time of start of execution, is used as current time
	 */
	define('MICROTIME', microtime(true));                //Time in seconds (float)
	define('TIME', floor(MICROTIME));                    //Time in seconds (integer)
	define('DIR', realpath(__DIR__.'/../cscms.travis')); //Root directory
}
chdir(DIR);

require_once DIR.'/core/loader_base.php';      //Inclusion of loader base
require_once DIR.'/core/functions_global.php'; //Inclusion of functions that work with global state
require_once __DIR__.'/Mock_object.php';
require_once __DIR__.'/Singleton.php';
require_once __DIR__.'/functions.php';

$_SERVER = [
	'HTTP_HOST'            => 'cscms.travis',
	'HTTP_ACCEPT_LANGUAGE' => 'en-us;q=0.5,en;q=0.3',
	'SERVER_NAME'          => 'cscms.travis',
	'SERVER_PROTOCOL'      => 'HTTP/1.1',
	'REQUEST_METHOD'       => 'GET',
	'QUERY_STRING'         => '',
	'REQUEST_URI'          => '/'
];
/**
 * Wrapper around default `$_SERVER` superglobal
 */
$_SERVER = new _SERVER($_SERVER);
if (!defined('DEBUG')) {
	define('DEBUG', false);
}

if (!defined('DOMAIN')) {
	define('DOMAIN', 'cscms.travis');
}
