<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
require_once __DIR__.'/../cscms.travis/core/traits/Singleton/Base.php';
require_once __DIR__.'/Singleton.php';
require_once __DIR__.'/../cscms.travis/core/bootstrap.php';
require_once __DIR__.'/Mock_object.php';
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

if (!defined('DEBUG')) {
	define('DEBUG', false);
}
