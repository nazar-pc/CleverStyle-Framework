<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
define('DIR',	__DIR__);
require_once	DIR.'/core/functions.php';
require_once	'Archive/Tar.php';
if (file_exists(DIR.'/cscms.phar.tar')) {
	unlink(DIR.'/cscms.phar.tar');
}
if (file_exists(DIR.'/cscms.phar')) {
	unlink(DIR.'/cscms.phar');
}
$version	= _json_decode(file_get_contents(DIR.'/components/modules/System/meta.json'))['version'];
(new Archive_Tar(DIR.'/cscms.phar.tar'))->createModify(
	array_merge(
		get_files_list(DIR.'/components/modules/System', false, 'f', true, true),
		get_files_list(DIR.'/core', '/^[^(ide)]/', 'f', true, true),
		get_files_list(DIR.'/includes', false, 'f', true, true),
		get_files_list(DIR.'/themes', false, 'f', true, true),
		[
			DIR.'/custom.php',
			DIR.'/install.php',
			DIR.'/favicon.ico',
			DIR.'/license.txt',
			DIR.'/readme.html',
			DIR.'/Storage.php'
		]
	),
	null,
	DIR
)->addString(
	'.htaccess',
	'AddDefaultCharset utf-8
Options -All -Multiviews +FollowSymLinks
IndexIgnore *.php *.pl *.cgi *.htaccess *.htpasswd

RewriteEngine On
RewriteBase /

<Files license.txt>
	RewriteEngine Off
</Files>
#<Files Storage.php>
#	RewriteEngine Off
#</Files>
<Files readme.html>
	RewriteEngine Off
</Files>
<Files favicon.ico>
	RewriteEngine Off
</Files>

php_value zlib.output_compression off

RewriteRule .* index.php

'
)->addString(
	'index.php',
	str_replace('$version$', $version, file_get_contents(DIR.'/index.php'))
);//TODO Repack into one more archive, add index.php, that will include *.phar file. install.php will be autoloader of phar file
(new Phar(DIR.'/cscms.phar.tar'))->convertToExecutable(Phar::TAR, Phar::BZ2, '.phar')->setSignatureAlgorithm(PHAR::SHA512);
unlink(DIR.'/cscms.phar.tar');
rename(DIR.'/cscms.phar', DIR.'/CleverStyle CMS '.$version.'.phar');
echo 'Done!';