<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
$htaccess = file_get_contents(DIR.'/.htaccess');
$htaccess = str_replace(
	'<FilesMatch "\.(css|js|gif|jpg|jpeg|png|ico|eot|ttc|ttf|svg|svgz|woff|woff2)$">',
	'<FilesMatch "\.(css|js|gif|jpg|jpeg|png|ico|svg|svgz|ttc|ttf|otf|woff|woff2|eot)$">',
	$htaccess
);
file_put_contents(DIR.'/.htaccess', $htaccess);
