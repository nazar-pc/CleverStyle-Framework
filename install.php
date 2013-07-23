<!doctype html>
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
echo	h::title('CleverStyle CMS $version$ Installation').
		h::meta([
			'charset'	=> 'utf-8'
		]).
		h::style(file_get_contents(DIR.'/install/style.css')).
		h::header(
			h::img([
				'src'	=> (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.
							$_SERVER['HTTP_HOST'].
							'/'.trim(str_replace('install.php', '', $_SERVER['REQUEST_URI']), '/').
							'/install/logo.png'
			]).
			h::h1('CleverStyle CMS $version$ Installation')
		).
		h::section(
			isset($_POST['site_name']) ? install_process() : install_form()
		).
		h::footer(
			'Copyright (c) 2011-2013, Nazar Mokrynskyi'
		);