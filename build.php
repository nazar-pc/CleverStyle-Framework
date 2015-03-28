<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     Builder
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
if (version_compare(PHP_VERSION, '5.4', '<')) {
	exit('CleverStyle CMS require PHP 5.4 or higher');
}
if (!Phar::canWrite()) {
	exit("CleverStyle CMS Builder can't work, set, please, \"phar.readonly=off\" option in \"php.ini\"\n");
}
define('DIR', __DIR__);
require_once DIR.'/build/Builder.php';
require_once DIR.'/core/thirdparty/nazarpc/BananaHTML.php';
require_once DIR.'/core/classes/h/Base.php';
require_once DIR.'/core/classes/h.php';
require_once DIR.'/core/thirdparty/upf.php';
require_once DIR.'/core/functions.php';
date_default_timezone_set('UTC');
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
$Builder = new cs\Builder(DIR);
$mode    = 'form';
$cli     = PHP_SAPI == 'cli';
if ($cli) {
	for ($i = 1; $i < $argc; $i += 2) {
		switch ($argv[$i]) {
			case '-h':
				$mode = 'form';
				break;
			case '-M':
				$mode = $argv[$i + 1];
				break;
			case '-m':
				$_POST['modules'] = explode(',', $argv[$i + 1]);
				break;
			case '-p':
				$_POST['plugins'] = explode(',', $argv[$i + 1]);
				break;
			case '-t':
				$_POST['themes'] = explode(',', $argv[$i + 1]);
				break;
			case '-s':
				$_POST['suffix'] = $argv[$i + 1];
				break;
		}
	}
	if ($mode == 'form') {
		exit(
		'CleverStyle CMS builder
Builder is used for creating distributive of the CleverStyle CMS and its components.
Usage: php build.php [-h] [-M <mode>] [-m <module>] [-p <plugin>] [-t <theme>] [-s <suffix>]
  -h - This information
  -M - Mode of builder, can be one of: core, module, plugin, theme
  -m - One or more modules names separated by coma
       If mode is "core" - specified modules will be included in distributive
       If mode is module - distributive of module will be created (only first module will be taken)
       In other modes ignored
  -p - One or more plugins names separated by coma
       If mode is "core" - specified plugins will be included in distributive
       If mode is plugin - distributive of plugin will be created (only first plugin will be taken)
       In other modes ignored
  -t - One or more themes names separated by coma
       If mode is "core" - specified themes will be included in distributive
       If mode is theme - distributive of theme will be created (only first theme will be taken)
       In other modes ignored
  -s - Suffix for distributive
Example:
  php build.php -M core
  php build.php -M core -m Plupload,Static_pages
  php build.php -M core -p TinyMCE -t DarkEnergy -s custom
'
		);
	} else {
		echo $Builder->$mode()."\n";
	}
	return;
}
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'core':
		case 'module':
		case 'plugin':
		case 'theme':
			$mode = $_POST['mode'];
	}
}
echo
	"<!doctype html>".
	h::title('CleverStyle CMS Builder').
	h::{'meta[charset=utf-8]'}().
	h::link(
		[
			'href' => 'build/includes/style.css',
			'rel'  => 'stylesheet'
		]
	).
	h::script(
		[
			'src'   => 'build/includes/functions.js',
			'level' => 0
		]
	)."\n".
	h::header(
		h::{'img[src=build/includes/logo.png]'}().
		h::h1('CleverStyle CMS Builder')
	).
	h::section(
		$Builder->$mode()
	).
	h::footer(
		'Copyright (c) 2011-2015, Nazar Mokrynskyi'
	);
