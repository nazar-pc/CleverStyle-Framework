<?php
/**
 * @package   CleverStyle Framework
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

file_put_contents(
	DIR.'/.htaccess',
	str_replace(
		'Header set Cache-Control "max-age=2592000, public"',
		'Header set Cache-Control "max-age=2592000, immutable"',
		file_get_contents(DIR.'/.htaccess')
	)
);
