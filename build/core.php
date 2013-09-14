<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
time_limit_pause();
$version			= _json_decode(file_get_contents(DIR.'/components/modules/System/meta.json'))['version'];
if (file_exists(DIR.'/build.phar')) {
	unlink(DIR.'/build.phar');
}
$phar				= new Phar(DIR.'/build.phar');
$length				= strlen(DIR.'/');
foreach (get_files_list(DIR.'/install', false, 'f', true, true) as $file) {
	$phar->addFile($file, substr($file, $length));
}
unset($file);
/**
 * Files to be included into installation package
 */
$list				= array_merge(
	get_files_list(DIR.'/components/modules/System', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/core', '/^[^(ide)]/', 'f', true, true, false, false, true),
	get_files_list(DIR.'/includes', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/templates', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/themes', false, 'f', true, true, false, false, true),
	[
		DIR.'/index.php',
		DIR.'/license.txt',
		DIR.'/Storage.php'
	]
);
/**
 * If composer.json exists - include it into installation build
 */
if (file_exists(DIR.'/composer.json')) {
	$list[]	= DIR.'/composer.json';
}
/**
 * Add selected modules that should be built-in into package
 */
$components_list	= [];
if (!empty($_POST['modules'])) {
	foreach ($_POST['modules'] as $i => $module) {
		if (is_dir(DIR."/components/modules/$module") && file_exists(DIR."/components/modules/$module/meta.json")) {
			unlink(DIR."/components/modules/$module/fs.json");
			$list_				= get_files_list(DIR."/components/modules/$module", false, 'f', true, true, false, false, true);
			file_put_contents(
				DIR."/components/modules/$module/fs.json",
				_json_encode(
					array_values(
						_substr(
							$list_,
							strlen(DIR."/components/modules/$module/")
						)
					)
				)
			);
			$list_[]			= DIR."/components/modules/$module/fs.json";
			$components_list	= array_merge(
				$components_list,
				$list_
			);
			unset($list_);
		} else {
			unset($_POST['modules'][$i]);
		}
	}
	unset($i, $module);
	$phar->addFromString('modules.json', _json_encode($_POST['modules']));
}
/**
 * Add selected plugins that should be built-in into package
 */
if (!empty($_POST['plugins'])) {
	foreach ($_POST['plugins'] as $plugin) {
		if (is_dir(DIR."/components/plugins/$plugin") && file_exists(DIR."/components/plugins/$plugin/meta.json")) {
			unlink(DIR."/components/plugins/$plugin/fs.json");
			$list_				= get_files_list(DIR."/components/plugins/$plugin", false, 'f', true, true, false, false, true);
			file_put_contents(
				DIR."/components/plugins/$plugin/fs.json",
				_json_encode(
					array_values(
						_substr(
							$list_,
							strlen(DIR."/components/plugins/$plugin/")
						)
					)
				)
			);
			$list_[]			= DIR."/components/plugins/$plugin/fs.json";
			$components_list	= array_merge(
				$components_list,
				$list_
			);
			unset($list_);
		}
	}
	unset($plugin);
}
/**
 * Joining system and components files list
 */
$list				= array_merge(
	$list,
	$components_list
);
/**
 * Addition files content into package
 */
$list				= array_map(
	function ($index, $file) use ($phar, $length) {
		$phar->addFromString('fs/'.$index, file_get_contents($file));
		return substr($file, $length);
	},
	array_keys($list),
	$list
);
/**
 * Addition of separate files into package
 */
$list[]				= 'readme.html';
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
/**
 * Fixing of system files list (without components files and core/fs.json file itself), it is needed for future system updating
 */
$list[]				= 'core/fs.json';
$phar->addFromString(
	'fs/'.(count($list)-1),
	_json_encode(
		array_flip(array_diff(array_slice($list, 0, -1), _substr($components_list, $length)))
	)
);
unset($components_list, $length);
/**
 * Addition of files, that are needed only for installation
 */
$list[]				= '.htaccess';
$phar->addFromString(
	'fs/'.(count($list)-1),
	'AddDefaultCharset utf-8
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
$list[]				= 'config/main.php';
$phar->addFromString(
	'fs/'.(count($list)-1),
	file_get_contents(DIR.'/config/main.php')
);
$list[]				= 'favicon.ico';
$phar->addFromString(
	'fs/'.(count($list)-1),
	file_get_contents(DIR.'/favicon.ico')
);
$list[]				= '.gitignore';
$phar->addFromString(
	'fs/'.(count($list)-1),
	file_get_contents(DIR.'/.gitignore')
);
$list[]				= 'custom.php';
$phar->addFromString(
	'fs/'.(count($list)-1),
	file_get_contents(DIR.'/custom.php')
);
/**
 * Flip array to have direct access to files by name during extracting and installation, and fixing of files list for installation
 */
$phar->addFromString('fs.json', _json_encode(array_flip($list)));
unset($list);
/**
 * Addition of supplementary files, that are needed directly for installation process: installer with GUI interface, readme, license, some additional
 * information about available languages, themes, current version of system
 */
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
$themes				= get_files_list(DIR.'/themes', false, 'd');
asort($themes);
$color_schemes		= [];
foreach ($themes as $theme) {
	$color_schemes[$theme]	= [];
	$color_schemes[$theme]	= get_files_list(DIR."/themes/$theme/schemes", false, 'd');
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
	'"'.$version.'"'
);
unset($themes, $theme, $color_schemes);
$phar				= $phar->convertToExecutable(Phar::TAR, Phar::BZ2, '.phar.tar');
unlink(DIR.'/build.phar');
$phar->setStub("<?php Phar::webPhar(null, 'install.php'); __HALT_COMPILER();");
$phar->setSignatureAlgorithm(PHAR::SHA512);
unset($phar);
rename(DIR.'/build.phar.tar', DIR."/CleverStyle_CMS_$version.phar.php");
echo "Done! CleverStyle CMS $version";