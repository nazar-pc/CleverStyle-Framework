<!doctype html>
<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
if (version_compare(PHP_VERSION, '5.4', '<')) {
	exit('CleverStyle CMS require PHP 5.4 or higher');
}
if (!Phar::canWrite()) {
	exit('CleverStyle CMS Builder can\'t work, set, please, <b>phar.readonly=off</b> option in <b>php.ini</b>');
}
define('DIR', __DIR__);
require_once DIR.'/core/classes/h/_Abstract.php';
require_once DIR.'/core/classes/h.php';
require_once DIR.'/core/upf.php';
require_once DIR.'/core/functions.php';
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
		h::meta([
			'charset'	=> 'utf-8'
		]).
		h::link([
			'href'	=> 'build/includes/style.css',
			'rel'	=> 'stylesheet'
		]).
		h::script([
			'src'	=> 'build/includes/functions.js',
			'level'	=> false
		])."\n".
		h::header(
			h::img([
				'src'	=> 'build/includes/logo.png'
			]).
			h::h1('CleverStyle CMS Builder')
		).
		h::section(
			ob_wrapper(function () use ($mode) {
				include_once DIR."/build/$mode.php";
			})
		).
		h::footer(
			'Copyright (c) 2011-2013, Nazar Mokrynskyi'
		);