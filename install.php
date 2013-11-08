<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Installer
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
if (version_compare(PHP_VERSION, '5.4', '<')) {
	exit('CleverStyle CMS require PHP 5.4 or higher');
}
if (count(explode('/', $_SERVER['REQUEST_URI'])) > 3) {
	exit('Installation into subdirectory is not supported!');
}
define('DIR',	__DIR__);														//Path to installer dir
$ROOT	= pathinfo(__DIR__, PATHINFO_DIRNAME);
mb_internal_encoding('utf-8');
define('ROOT',	mb_strpos($ROOT, 'phar://') === 0 ? substr($ROOT, 7) : $ROOT);	//Path to site root
unset($ROOT);
global $fs;
$fs		= json_decode(file_get_contents(DIR.'/fs.json'), true);
require_once DIR.'/fs/'.$fs['core/upf.php'];
require_once DIR.'/fs/'.$fs['core/functions.php'];
require_once DIR.'/fs/'.$fs['core/classes/h/_Abstract.php'];
require_once DIR.'/fs/'.$fs['core/classes/h.php'];
require_once DIR.'/install/functions.php';
date_default_timezone_set('UTC');
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
echo	"<!doctype html>\n".
		h::title('CleverStyle CMS $version$ Installation').
		h::meta([
			'charset'	=> 'utf-8'
		]).
		h::style(file_get_contents(DIR.'/install/style.css')).
		h::header(
			h::img([
				'src'	=> 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
			]).
			h::h1('CleverStyle CMS $version$ Installation')
		).
		h::section(
			isset($_POST['site_name']) ? install_process() : install_form()
		).
		h::footer(
			'Copyright (c) 2011-2013, Nazar Mokrynskyi'
		);