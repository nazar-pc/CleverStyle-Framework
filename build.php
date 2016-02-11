<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Builder
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
if (!Phar::canWrite()) {
	echo "CleverStyle CMS Builder can't work, set, please, \"phar.readonly=off\" option in \"php.ini\"";
	return;
}
define('DIR', __DIR__);
require_once DIR.'/build/Builder.php';
require_once DIR.'/core/thirdparty/nazarpc/BananaHTML.php';
require_once DIR.'/core/classes/h/Base.php';
require_once DIR.'/core/classes/h.php';
require_once DIR.'/core/thirdparty/upf.php';
require_once DIR.'/core/functions.php';
time_limit_pause();
date_default_timezone_set('UTC');
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
$Builder = new cs\Builder(DIR, DIR);
if (PHP_SAPI == 'cli') {
	$options = getopt(
		'hM:m:p:t:s:',
		[
			'help',
			'mode:',
			'modules:',
			'plugins:',
			'themes:',
			'suffix:'
		]
	);
	$mode    = @$options['M'] ?: @$options['mode'];
	$modules = array_filter(explode(',', @$options['m'] ?: @$options['modules']));
	$plugins = array_filter(explode(',', @$options['p'] ?: @$options['plugins']));
	$themes  = array_filter(explode(',', @$options['t'] ?: @$options['themes']));
	/** @noinspection NestedTernaryOperatorInspection */
	$suffix = (@$options['s'] ?: @$options['suffix']) ?: null;
	if (
		@$options['h'] ||
		@$options['help'] ||
		!in_array($mode, ['core', 'module', 'plugin', 'theme'])
	) {
		echo <<<HELP
CleverStyle CMS builder
Builder is used for creating distributive of the CleverStyle CMS and its components.
Usage: php build.php [-h] [-M <mode>] [-m <module>] [-p <plugin>] [-t <theme>] [-s <suffix>]
  -h
  --help    - This information
  -M
  --mode    - Mode of builder, can be one of: core, module, plugin, theme
  -m
  --modules - One or more modules names separated by coma
       If mode is "core" - specified modules will be included into system distributive
       If mode is module - distributive of each module will be created
       In other modes ignored
  -p
  --plugins - One or more plugins names separated by coma
       If mode is "core" - specified plugins will be included into system distributive
       If mode is plugin - distributive of each plugin will be created
       In other modes ignored
  -t
  --themes  - One or more themes names separated by coma
       If mode is "core" - specified themes will be included into system distributive
       If mode is theme - distributive each each theme will be created
       In other modes ignored
  -s
  --suffix  - Suffix for distributive
Example:
  php build.php -M core
  php build.php -M core -m Plupload,Static_pages
  php build.php -M core -p TinyMCE -t DarkEnergy -s custom
  php build.php -M module -m Plupload,Static_pages
HELP;
	} elseif ($mode == 'core') {
		echo $Builder->core($modules, $plugins, $themes, $suffix)."\n";
	} else {
		foreach (${$mode.'s'} as $component) {
			echo $Builder->$mode($component, $suffix)."\n";
		}
	}
} else {
	$content = '';
	$mode    = @$_POST['mode'] ?: 'form';
	if ($mode == 'core') {
		$content = $Builder->core(@$_POST['modules'] ?: [], @$_POST['plugins'] ?: [], @$_POST['themes'] ?: [], @$_POST['suffix']);
	} elseif (in_array($mode, ['core', 'module', 'plugin', 'theme'])) {
		foreach (@$_POST[$mode.'s'] as $component) {
			$content .= $Builder->$mode($component, @$_POST['suffix']).h::br();
		}
	} else {
		$content = $Builder->form();
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
				'src' => 'build/includes/functions.js'
			]
		)."\n".
		h::header(
			h::{'img[src=build/includes/logo.png]'}().
			h::h1('CleverStyle CMS Builder')
		).
		h::section($content).
		h::footer(
			'Copyright (c) 2011-2016, Nazar Mokrynskyi'
		);
}
