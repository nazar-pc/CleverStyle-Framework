<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
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
\cs\Singleton\clean_classes_cache();
require __DIR__.'/Request.php';                  //Inclusion of Request class, used for http server requests processing
require __DIR__.'/Singleton.php';                //Inclusion of `Singleton` trait, specific for http server
require __DIR__.'/Config.php';                   //Inclusion of `cs\custom\Config` class, which is used instead original `cs\Config` for http server
require __DIR__.'/User.php';                     //Inclusion of `cs\custom\User` class, which is used instead original `cs\User` for http server
/**
 * Including of custom files
 */
foreach (glob(CUSTOM.'/*.php') ?: [] as $custom) {
	include $custom;
}
unset($custom);
