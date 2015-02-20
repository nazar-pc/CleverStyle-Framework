<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * This is custom loader that includes basic files and defines constants,
 * but do not call any class to leave that all for Http server
 */
namespace cs\modules\Http_server;
require DIR.'/core/loader_base.php';             //Inclusion of loader base
@ini_set('error_log', LOGS.'/Http_server.log');
require __DIR__.'/functions.php';                //Inclusion of functions needed for http server
clean_classes_cache();
require __DIR__.'/Request.php';                  //Inclusion of Request class, used for http server requests processing
require __DIR__.'/Singleton.php';                //Inclusion of `Singleton` trait, specific for http server
if (!ASYNC_HTTP_SERVER) {
	require __DIR__.'/Config.php';               //Inclusion of `cs\custom\Config` class, which is used instead original `cs\Config` for http server
}
/**
 * Including of custom files
 */
foreach (glob(CUSTOM.'/*.php') ?: [] as $custom) {
	include $custom;
}
unset($custom);
if (ASYNC_HTTP_SERVER) {
	require __DIR__.'/Superglobals_wrapper.php'; //Inclusion of wrapper for `$_SERVER` `$_GET`, `$_POST`, `$_REQUEST` for http server
	$_GET     = new Superglobals_wrapper();
	$_POST    = new Superglobals_wrapper();
	$_REQUEST = new Superglobals_wrapper();
	$_SERVER  = new Superglobals_wrapper();
}
