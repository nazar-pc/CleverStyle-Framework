<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
define('DIR',	__DIR__);
require_once	DIR.'/core/classes/class.h.php';
require_once	DIR.'/core/functions.php';
require_once	'Archive/Tar.php';
$version	= _json_decode(file_get_contents(DIR.'/components/modules/System/meta.json'))['version'];
$tar		= new Archive_Tar(DIR.'/cscms.phar.tar');
$tar->createModify(
	array_merge(
		get_files_list(DIR.'/install', false, 'f', true, true)
	),
	null,
	DIR
);
$list		= array_merge(
	get_files_list(DIR.'/components/modules/System', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/core', '/^[^(ide)]/', 'f', true, true, false, false, true),
	get_files_list(DIR.'/includes', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/templates', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/themes', false, 'f', true, true, false, false, true),
	[
		DIR.'/config/main.php',
		DIR.'/custom.php',
		DIR.'/favicon.ico',
		DIR.'/license.txt',
		DIR.'/Storage.php'
	]
);
$length		= strlen(DIR.'/');
$list		= array_map(
	function ($index, $file) use ($tar, $length) {
		$tar->addString('system/'.$index, file_get_contents($file));
		return substr($file, $length);
	},
	array_keys($list),
	$list
);
unset($length);
$list[]		= '.htaccess';
$tar->addString(
	'system/'.(count($list)-1),
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
);
$list[]		= 'index.php';
$tar->addString(
	'system/'.(count($list)-1),
	str_replace('$version$', $version, file_get_contents(DIR.'/index.php'))
);
$list[]		= 'readme.html';
$tar->addString(
	'system/'.(count($list)-1),
	str_replace(
		[
			'$version$',
			'$image$'
		],
		[
			$version,
			h::img([
				'src'	=> 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
			])
		],
		file_get_contents(DIR.'/readme.html')
	)
);
$tar->addString(
	'languages.json',
	_json_encode(
		array_merge(
			_mb_substr(get_files_list(DIR.'/core/languages', '/^lang\..*?\.php$/i', 'f'), 5, -4) ?: [],
			_mb_substr(get_files_list(DIR.'/core/languages', '/^lang\..*?\.json$/i', 'f'), 5, -5) ?: []
		)
	)
);
$tar->addString(
	'db_engines.json',
	_json_encode(
		_mb_substr(get_files_list(DIR.'/core/engines/DB', '/^[^_].*?\.php$/i', 'f'), 0, -4)
	)
);
$tar->addString('system.json', _json_encode(array_flip($list)));
$tar->addString(
	'install.php',
	str_replace('$version$', $version, file_get_contents(DIR.'/install.php'))
);
$tar->addString(
	'readme.html',
	str_replace(
		[
			'$version$',
			'$image$'
		],
		[
			$version,
			h::img([
				'src'	=> 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
			])
		],
		file_get_contents(DIR.'/readme.html')
	)
);
$tar->addString(
	'license.txt',
	file_get_contents(DIR.'/license.txt')
);
$themes							= get_files_list(DIR.'/themes', false, 'd');
asort($themes);
$color_schemes					= [];
foreach ($themes as $theme) {
	$color_schemes[$theme]	= [];
	$color_schemes[$theme]	= get_files_list(DIR.'/themes/'.$theme.'/schemes', false, 'd');
	asort($color_schemes[$theme]);
}
$tar->addString(
	'themes.json',
	_json_encode($themes)
);
$tar->addString(
	'color_schemes.json',
	_json_encode($color_schemes)
);
unset($themes, $theme, $color_schemes, $tar);
$phar		= new Phar(DIR.'/cscms.phar.tar');
$phar->convertToExecutable(Phar::TAR, Phar::BZ2, '.phar');
unlink(DIR.'/cscms.phar.tar');
unset($phar);
$phar		= new Phar(DIR.'/cscms.phar');
$phar->setStub("<?php Phar::webPhar(null, 'install.php'); __HALT_COMPILER();");
$phar->setSignatureAlgorithm(PHAR::SHA512);
unset($phar);
rename(DIR.'/cscms.phar', DIR.'/CleverStyle CMS '.$version.'.phar.php');
echo 'Done! Version: '.$version;