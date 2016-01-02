<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     Test
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
/**
 * Simulate $_SERVER content like in regular HTTP request to home page
 */
$_SERVER = new _SERVER([
	'HTTP_HOST'             => 'cscms.travis',
	'HTTP_USER_AGENT'       => 'CleverStyle CMS test',
	'HTTP_ACCEPT_LANGUAGE'  => 'en-us;q=0.5,en;q=0.3',
	'SERVER_NAME'           => 'cscms.travis',
	'REMOTE_ADDR'           => '127.0.0.1',
	'DOCUMENT_ROOT'         => realpath(__DIR__.'/../cscms.travis'),
	'SERVER_PROTOCOL'       => 'HTTP/1.1',
	'REQUEST_METHOD'        => 'GET',
	'QUERY_STRING'          => '',
	'REQUEST_URI'           => '/'
]);
