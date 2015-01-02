<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     Builder
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
time_limit_pause();
$version = file_get_json(DIR.'/components/modules/System/meta.json')['version'];
if (file_exists(DIR.'/build.phar')) {
	unlink(DIR.'/build.phar');
}
$phar   = new Phar(DIR.'/build.phar');
$length = mb_strlen(DIR.'/');
foreach (get_files_list(DIR.'/install', false, 'f', true, true) as $file) {
	$phar->addFile($file, mb_substr($file, $length));
}
unset($file);
/**
 * Files to be included into installation package
 */
$list = array_merge(
	get_files_list(DIR.'/components/modules/System', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/core', '/^[^(ide)]/', 'f', true, true, false, false, true),
	get_files_list(DIR.'/custom', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/includes', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/templates', false, 'f', true, true, false, false, true),
	get_files_list(DIR.'/themes/CleverStyle', false, 'f', true, true, false, false, true),
	[
		DIR.'/components/blocks/.gitkept',
		DIR.'/components/plugins/.gitkept',
		DIR.'/index.php',
		DIR.'/license.txt',
		DIR.'/Storage.php'
	]
);
/**
 * If composer.json exists - include it into installation build
 */
if (file_exists(DIR.'/composer.json')) {
	$list[] = DIR.'/composer.json';
}
/**
 * If composer.lock exists - include it into installation build
 */
if (file_exists(DIR.'/composer.lock')) {
	$list[] = DIR.'/composer.lock';
}
/**
 * Add selected modules that should be built-in into package
 */
$components_list = [];
if (@$_POST['modules']) {
	foreach ($_POST['modules'] as $i => $module) {
		if ($module != 'System' && is_dir(DIR."/components/modules/$module") && file_exists(DIR."/components/modules/$module/meta.json")) {
			@unlink(DIR."/components/modules/$module/fs.json");
			$list_ = get_files_list(DIR."/components/modules/$module", false, 'f', true, true, false, false, true);
			file_put_json(
				DIR."/components/modules/$module/fs.json",
				array_values(
					_mb_substr(
						$list_,
						mb_strlen(DIR."/components/modules/$module/")
					)
				)
			);
			$list_[]         = DIR."/components/modules/$module/fs.json";
			$components_list = array_merge(
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
if (@$_POST['plugins']) {
	foreach ($_POST['plugins'] as $plugin) {
		if (is_dir(DIR."/components/plugins/$plugin") && file_exists(DIR."/components/plugins/$plugin/meta.json")) {
			@unlink(DIR."/components/plugins/$plugin/fs.json");
			$list_ = get_files_list(DIR."/components/plugins/$plugin", false, 'f', true, true, false, false, true);
			file_put_json(
				DIR."/components/plugins/$plugin/fs.json",
				array_values(
					_mb_substr(
						$list_,
						mb_strlen(DIR."/components/plugins/$plugin/")
					)
				)
			);
			$list_[]         = DIR."/components/plugins/$plugin/fs.json";
			$components_list = array_merge(
				$components_list,
				$list_
			);
			unset($list_);
		}
	}
	unset($plugin);
}
/**
 * Add selected themes that should be built-in into package
 */
if (@$_POST['themes']) {
	foreach ($_POST['themes'] as $theme) {
		if (is_dir(DIR."/themes/$theme") && file_exists(DIR."/themes/$theme/meta.json")) {
			@unlink(DIR."/themes/$theme/fs.json");
			$list_ = get_files_list(DIR."/themes/$theme", false, 'f', true, true, false, false, true);
			file_put_json(
				DIR."/themes/$theme/fs.json",
				array_values(
					_mb_substr(
						$list_,
						mb_strlen(DIR."/themes/$theme/")
					)
				)
			);
			$list_[]         = DIR."/themes/$theme/fs.json";
			$components_list = array_merge(
				$components_list,
				$list_
			);
			unset($list_);
		}
	}
	unset($theme);
}
/**
 * Joining system and components files list
 */
$list = array_merge(
	$list,
	$components_list
);
/**
 * Addition files content into package
 */
$list = array_map(
	function ($index, $file) use ($phar, $length) {
		/**
		 * TODO: `f` here and in other places in this file before index is added as hack for HHVM in order to allow installation/upgrading of components
		 * TODO: and should be removed when bug (extracting files with name `0`) fixed upstream
		 */
		$phar->addFromString("fs/f$index", file_get_contents($file));
		return substr($file, $length);
	},
	array_keys($list),
	$list
);
/**
 * Addition of separate files into package
 */
$list[] = 'readme.html';
$phar->addFromString(
	'fs/f'.(count($list) - 1),
	str_replace(
		[
			'$version$',
			'$image$'
		],
		[
			$version,
			h::img([
				'src' => 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
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
$list[] = 'core/fs.json';
$phar->addFromString(
	'fs/f'.(count($list) - 1),
	_json_encode(
		array_map(
			function ($file_index) {
				/**
				 * TODO: `f` before index is added as hack for HHVM in order to allow installation/upgrading of components
				 * TODO: and should be removed when bug (extracting files with name `0`) fixed upstream
				 */
				return "f$file_index";
			},
			array_flip(array_diff(array_slice($list, 0, -1), _substr($components_list, $length)))
		)
	)
);
unset($components_list, $length);
/**
 * Addition of files, that are needed only for installation
 */
$list[] = '.htaccess';
$phar->addFromString(
	'fs/f'.(count($list) - 1),
	'AddDefaultCharset utf-8
Options -Indexes -Multiviews +FollowSymLinks
IndexIgnore *.php *.pl *.cgi *.htaccess *.htpasswd

RewriteEngine On
RewriteBase /

<FilesMatch ".*/.*">
	Options -FollowSymLinks
</FilesMatch>
<FilesMatch "\.(css|js|gif|jpg|jpeg|png|ico|eot|ttc|ttf|svg|svgz|woff)$">
	RewriteEngine Off
</FilesMatch>
<Files license.txt>
	RewriteEngine Off
</Files>
#<Files Storage.php>
#	RewriteEngine Off
#</Files>

RewriteRule .* index.php
'
);
$list[] = 'config/main.php';
$phar->addFromString(
	'fs/f'.(count($list) - 1),
	file_get_contents(DIR.'/config/main.php')
);
$list[] = 'favicon.ico';
$phar->addFromString(
	'fs/f'.(count($list) - 1),
	file_get_contents(DIR.'/favicon.ico')
);
$list[] = '.gitignore';
$phar->addFromString(
	'fs/f'.(count($list) - 1),
	file_get_contents(DIR.'/.gitignore')
);
/**
 * Flip array to have direct access to files by name during extracting and installation, and fixing of files list for installation
 */
$phar->addFromString('fs.json', _json_encode(
	array_map(
		function ($file_index) {
			return "f$file_index";
		},
		array_flip($list))
	)
);
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
				'src' => 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
			])
		],
		file_get_contents(DIR.'/readme.html')
	)
);
$phar->addFromString(
	'license.txt',
	file_get_contents(DIR.'/license.txt')
);
$themes = get_files_list(DIR.'/themes', false, 'd');
asort($themes);
$phar->addFromString(
	'themes.json',
	_json_encode($themes)
);
$phar->addFromString(
	'version',
	"\"$version\""
);
unset($themes, $theme);
$phar->setStub(
"<?php
if (PHP_SAPI == 'cli') {
	Phar::mapPhar('cleverstyle_cms.phar');
	include 'phar://cleverstyle_cms.phar/install.php';
} else {
	Phar::webPhar(null, 'install.php');
}
__HALT_COMPILER();"
);
unset($phar);
$suffix = @$_POST['suffix'] ? "_$_POST[suffix]" : '';
rename(DIR.'/build.phar', DIR."/CleverStyle_CMS_$version$suffix.phar.php");
echo "Done! CleverStyle CMS $version";
