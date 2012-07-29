<!doctype html>
<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
define('DIR', __DIR__);
require_once DIR.'/core/functions.php';
require_once DIR.'/core/classes/class.h.php';
require_once DIR.'/install/functions.php';
date_default_timezone_set('UTC');
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
mb_internal_encoding('utf-8');
echo h::html(
	h::head(
		h::title('CleverStyle CMS Installation').
		h::style(file_get_contents(DIR.'/install/style.css'))
	).
	h::body(
		h::header(
			h::img([
				'src'	=> 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
			]).
			h::h1('CleverStyle CMS Installation')
		).
		(isset($_POST['site_name']) ? install_process() : install_form())
	)
);