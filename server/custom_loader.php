<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
require DIR.'/core/loader_base.php'; //Inclusion of loader base
require DIR.'/server/functions.php'; //Inclusion of functions needed for http server
require DIR.'/server/_SERVER.php';   //Inclusion of `$_SERVER` wrapper, specific for http server
require DIR.'/server/Request.php';   //Inclusion of Request class, used for http server requests processing
require DIR.'/server/Singleton.php'; //Inclusion of `Singleton` trait, specific for http server
require DIR.'/server/Config.php';    //Inclusion of `cs\custom\Config` class, which is used instead original `cs\Config` for http server
/**
 * Including of custom files
 */
foreach (glob(CUSTOM.'/*.php') ?: [] as $custom) {
	include $custom;
}
unset($custom);
shutdown_function(false);
