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
use
	cs\Core;

require DIR.'/core/loader_base.php';
@ini_set('error_log', LOGS.'/Http_server.log');
/**
 * Including of custom files
 */
foreach (glob(CUSTOM.'/*.php') ?: [] as $custom) {
	include $custom;
}
unset($custom);
Core::instance();
