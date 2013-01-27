<!doctype html>
<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
define('DIR',	__DIR__);
require_once    DIR.'/core/classes/class.h.php';
require_once    DIR.'/core/functions.php';
date_default_timezone_set('UTC');
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
$mode	= 'form';
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'core':
		case 'module':
		case 'plugin':
			$mode	= $_POST['mode'];
	}
}
echo	h::title('CleverStyle CMS Builder').
		h::link([
			'href'	=> 'build/style.css',
			'rel'	=> 'stylesheet'
		]).
		h::script([
			'src'	=> 'build/functions.js',
			'level'	=> false
		])."\n".
		h::header(
			h::img([
				'src'	=> (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.
							$_SERVER['HTTP_HOST'].
							'/'.trim(str_replace('install.php', '', $_SERVER['REQUEST_URI']), '/').
							'/install/logo.png'
			]).
			h::h1('CleverStyle CMS Builder')
		).
		h::section(
			ob_wrapper(function () use ($mode) {
				include_once DIR.'/build/'.$mode.'.php';
			})
		).
		h::footer(
			'Copyright (c) 2011-2013, Nazar Mokrynskyi'
		);