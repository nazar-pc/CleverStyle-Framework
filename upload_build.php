<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     Builder
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
if (substr(PHP_VERSION, 0, 3) !== '5.6') {
	exit("Distributive is uploaded only under PHP 5.6\n");
}
$branch = exec('git symbolic-ref --short HEAD');
if ($branch != 'master') {
	exit("Distributive is uploaded only when on master branch\n");
}
$tag = exec('git describe --tags --exact-match HEAD 2>/dev/null');
ob_start();

define('DIR', __DIR__);
require_once DIR.'/build/Builder.php';
require_once DIR.'/core/thirdparty/nazarpc/BananaHTML.php';
require_once DIR.'/core/classes/h/Base.php';
require_once DIR.'/core/classes/h.php';
require_once DIR.'/core/thirdparty/upf.php';
require_once DIR.'/core/functions.php';

$modules = array_values(
	array_filter(
		get_files_list(DIR.'/components/modules', false, 'd'),
		function ($module) {
			return $module != 'System';
		}
	)
);
$plugins = get_files_list(DIR.'/components/plugins', false, 'd');
$themes  = array_values(
	array_filter(
		get_files_list(DIR.'/themes', false, 'd'),
		function ($theme) {
			return $theme != 'CleverStyle';
		}
	)
);

$Builder = new cs\Builder(DIR, DIR.'/dist');
$Builder->core([], [], [], 'Core');
$Builder->core($modules, $plugins, $themes, 'Full');
foreach ($modules as $module) {
	$Builder->module($module);
}
foreach ($plugins as $plugin) {
	$Builder->plugin($plugin);
}
foreach ($themes as $theme) {
	$Builder->theme($theme);
}
if (ob_get_contents()) {
	echo "Build failed:\n";
	exit(ob_get_clean());
}
exec('openssl enc -d -aes-256-cbc -in id_rsa.enc -out id_rsa -pass env:KEYPASS 2>/dev/null');
exec("rsync -e 'ssh -o StrictHostKeyChecking=no -i id_rsa' --compress --delete --recursive --progress dist/ nazar-pc@frs.sourceforge.net:/home/frs/project/cleverstyle-cms/latest/");
if ($tag) {
	exec("rsync -e 'ssh -o StrictHostKeyChecking=no -i id_rsa' --compress --delete --recursive --progress dist/ nazar-pc@frs.sourceforge.net:/home/frs/project/cleverstyle-cms/$tag/");
}
