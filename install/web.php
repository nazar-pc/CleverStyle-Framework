<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Installer
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	h;

if (count(explode('/', $_SERVER['REQUEST_URI'])) > 3) {
	echo 'Installation into subdirectory is not supported!';
	return;
}
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
$version = file_get_json(__DIR__.'/../meta.json')['version'];
echo
	"<!doctype html>\n".
	h::title("CleverStyle CMS $version Installation").
	h::meta(
		[
			'charset' => 'utf-8'
		]
	).
	h::style(file_get_contents(__DIR__.'/../install/style.css')).
	h::header(
		h::img(
			[
				'src' => 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(__DIR__.'/../install/logo.png'))
			]
		).
		h::h1("CleverStyle CMS $version Installation")
	).
	h::section(
		isset($_POST['site_name']) ? install_process() : install_form()
	).
	h::footer(
		'Copyright (c) 2011-2016, Nazar Mokrynskyi'
	);
