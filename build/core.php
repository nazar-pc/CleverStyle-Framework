<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
time_limit_pause();
$version		= _json_decode(file_get_contents(DIR.'/components/modules/System/meta.json'))['version'];
if (file_exists(DIR.'/build.phar')) {
	unlink(DIR.'/build.phar');
}
$phar			= new Phar(DIR.'/build.phar');
$length			= strlen(DIR.'/');
foreach (get_files_list(DIR.'/install', false, 'f', true, true) as $file) {
	$phar->addFile($file, substr($file, $length));
}
unset($file);
$list			= array_merge(
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
if (!empty($_POST['modules'])) {
	foreach ($_POST['modules'] as $module) {
		if (is_dir(DIR.'/components/modules/'.$module)) {
			$list		= array_merge(
				$list,
				get_files_list(DIR.'/components/modules/'.$module, false, 'f', true, true, false, false, true)
			);
		}
	}
	unset($module);
	$phar->addFromString('modules.json', _json_encode($_POST['modules']));
}
if (!empty($_POST['plugins'])) {
	foreach ($_POST['plugins'] as $plugin) {
		if (is_dir(DIR.'/components/plugins/'.$plugin)) {
			$list		= array_merge(
				$list,
				get_files_list(DIR.'/components/plugins/'.$plugin, false, 'f', true, true, false, false, true)
			);
		}
	}
	unset($plugin);
}
$list			= array_map(
	function ($index, $file) use ($phar, $length) {
		$phar->addFromString('fs/'.$index, file_get_contents($file));
		return substr($file, $length);
	},
	array_keys($list),
	$list
);
unset($length);
$list[]			= '.htaccess';
$phar->addFromString(
	'fs/'.(count($list)-1),
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
$list[]			= 'index.php';
$phar->addFromString(
	'fs/'.(count($list)-1),
	str_replace('$version$', $version, file_get_contents(DIR.'/index.php'))
);
$list[]			= 'readme.html';
$phar->addFromString(
	'fs/'.(count($list)-1),
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
$phar->addFromString(
	'languages.json',
	_json_encode(
		array_merge(
			_mb_substr(get_files_list(DIR.'/core/languages', '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
			_mb_substr(get_files_list(DIR.'/core/languages', '/^.*?\.json$/i', 'f'), 0, -5) ?: []
		)
	)
);
$phar->addFromString(
	'db_engines.json',
	_json_encode(
		_mb_substr(get_files_list(DIR.'/core/engines/DB', '/^[^_].*?\.php$/i', 'f'), 0, -4)
	)
);
$phar->addFromString('fs.json', _json_encode(array_flip($list)));
unset($list);
$phar->addFromString(
	'install.php',
	str_replace('$version$', $version, file_get_contents(DIR.'/install.php'))
);
$phar->addFromString(
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
$phar->addFromString(
	'license.txt',
	file_get_contents(DIR.'/license.txt')
);
$themes			= get_files_list(DIR.'/themes', false, 'd');
asort($themes);
$color_schemes	= [];
foreach ($themes as $theme) {
	$color_schemes[$theme]	= [];
	$color_schemes[$theme]	= get_files_list(DIR.'/themes/'.$theme.'/schemes', false, 'd');
	asort($color_schemes[$theme]);
}
$phar->addFromString(
	'themes.json',
	_json_encode($themes)
);
$phar->addFromString(
	'color_schemes.json',
	_json_encode($color_schemes)
);
$phar->addFromString(
	'version',
	$version
);
unset($themes, $theme, $color_schemes);
$phar		= $phar->convertToExecutable(Phar::TAR, Phar::BZ2, '.phar.tar');
unlink(DIR.'/build.phar');
$phar->setStub("<?php Phar::webPhar(null, 'install.php'); __HALT_COMPILER();");
$phar->setSignatureAlgorithm(PHAR::SHA512);
unset($phar);
rename(DIR.'/build.phar.tar', DIR.'/CleverStyle_CMS_'.$version.'.phar.php');
echo 'Done! CleverStyle CMS '.$version;