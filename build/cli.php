<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Builder
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;

$Builder = new Builder(DIR, DIR);
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
	isset($options['h']) ||
	isset($options['help']) ||
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
