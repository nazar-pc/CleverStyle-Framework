<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     Test
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
/**
 * Simulate $_SERVER content like in regular HTTP request to home page
 */
$_SERVER = [
	'REDIRECT_STATUS'       => '200',
	'HTTP_HOST'             => 'cscms.travis',
	'HTTP_USER_AGENT'       => 'CleverStyle CMS test',
	'HTTP_ACCEPT'           => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
	'HTTP_ACCEPT_LANGUAGE'  => 'en-us;q=0.5,en;q=0.3',
	'HTTP_ACCEPT_ENCODING'  => 'gzip, deflate',
	'HTTP_DNT'              => '1',
	'HTTP_COOKIE'           => '',
	'HTTP_CONNECTION'       => 'keep-alive',
	'HTTP_CACHE_CONTROL'    => 'max-age=0',
	'PATH'                  => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
	'SERVER_SOFTWARE'       => 'Apache/2.4.10 (Ubuntu)',
	'SERVER_NAME'           => 'cscms.travis',
	'SERVER_ADDR'           => '127.0.0.1',
	'SERVER_PORT'           => '80',
	'REMOTE_ADDR'           => '127.0.0.1',
	'DOCUMENT_ROOT'         => realpath(__DIR__.'/../cscms.travis'),
	'REQUEST_SCHEME'        => 'http',
	'CONTEXT_DOCUMENT_ROOT' => realpath(__DIR__.'/../cscms.travis'),
	'SERVER_ADMIN'          => 'webmaster@localhost',
	'SCRIPT_FILENAME'       => realpath(__DIR__.'/../cscms.travis/index.php'),
	'REMOTE_PORT'           => '53649',
	'REDIRECT_URL'          => '/',
	'GATEWAY_INTERFACE'     => 'CGI/1.1',
	'SERVER_PROTOCOL'       => 'HTTP/1.1',
	'REQUEST_METHOD'        => 'GET',
	'QUERY_STRING'          => '',
	'REQUEST_URI'           => '/',
	'SCRIPT_NAME'           => '/index.php',
	'PHP_SELF'              => '/index.php',
	'REQUEST_TIME_FLOAT'    => microtime(true),
	'REQUEST_TIME'          => time(),
];
