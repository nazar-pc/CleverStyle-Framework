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
	 * but do not call any class to leave that all for test cases, and unregisters shutdown function
	 */
/**
 * Time of start of execution, is used as current time
 */
define('MICROTIME', microtime(true));                //Time in seconds (float)
define('TIME', floor(MICROTIME));                    //Time in seconds (integer)
define('DIR', realpath(__DIR__.'/../cscms.travis')); //Root directory
chdir(DIR);

require DIR.'/core/loader_base.php';      //Inclusion of loader base
require DIR.'/core/functions_global.php'; //Inclusion of functions that work with global state
$_SERVER = [
	'HTTP_HOST'            => 'cscms.travis',
	'HTTP_USER_AGENT'      => 'CleverStyle CMS test',
	'HTTP_ACCEPT_LANGUAGE' => 'en-us;q=0.5,en;q=0.3',
	'SERVER_NAME'          => 'cscms.travis',
	'REMOTE_ADDR'          => '127.0.0.1',
	'DOCUMENT_ROOT'        => realpath(__DIR__.'/../cscms.travis'),
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
/**
 * Will allow headers sending, and will output buffered content before exit anyway
 */
ob_start();
include __DIR__.'/Mock_object.php';
include __DIR__.'/Singleton.php';

function do_request () {
	try {
		try {
			Request::instance()->init_from_globals();
			Response::instance()->init_with_typical_default_settings();
			Index::instance();
			Index::instance(true)->__finish();
			Page::instance()->__finish();
			User::instance(true)->__finish();
		} catch (ExitException $e) {
			if ($e->getCode() >= 400) {
				Page::instance()->error($e->getMessage() ?: null, $e->getJson(), $e->getCode());
			}
		}
	} catch (ExitException $e) {
	}
}
