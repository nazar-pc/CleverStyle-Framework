<?php
global	$DB_HOST,
		$DB_TYPE,
		$DB_NAME,
		$DB_USER,
		$DB_PASSWORD,
		$DB_PREFIX,
		$DB_CODEPAGE,
		
		$LANGUAGE,

		$CACHE_ENGINE,
		$CACHE_SIZE,

		$KEY;

define('DOMAIN', 'cscms.org');

$DB_HOST		= 'localhost';
$DB_TYPE		= 'MySQL';
$DB_NAME		= 'CleverStyle';
$DB_USER		= 'CleverStyle';
$DB_PASSWORD	= '1111';
$DB_PREFIX		= 'prefix_';
$DB_CODEPAGE	= 'utf8';
$LANGUAGE		= 'Русский';
$CACHE_ENGINE	= 'FileSystem';
$CACHE_SIZE		= 5;				//Cache size in MB, 0 means without limitation
$KEY			= 'f40fbea2ee5a24ce581fb53510883dfcf40fbea2ee5a24ce581fb535';