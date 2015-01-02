<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     Test
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
/**
 * Simulate $_SERVER content like in regular HTTP request to home page
 */
$_SERVER = [
	'HTTP_HOST'             => 'cscms.travis',
	'HTTP_USER_AGENT'       => 'CleverStyle CMS test',
	'HTTP_ACCEPT_LANGUAGE'  => 'en-us;q=0.5,en;q=0.3',
	'HTTP_DNT'              => '1',
	'HTTP_COOKIE'           => '',
	'SERVER_SOFTWARE'       => 'Apache/2.4.10 (Ubuntu)',
	'SERVER_NAME'           => 'cscms.travis',
	'REMOTE_ADDR'           => '127.0.0.1',
	'DOCUMENT_ROOT'         => realpath(__DIR__.'/../cscms.travis'),
	'SERVER_PROTOCOL'       => 'HTTP/1.1',
	'REQUEST_METHOD'        => 'GET',
	'QUERY_STRING'          => '',
	'REQUEST_URI'           => '/'
];
