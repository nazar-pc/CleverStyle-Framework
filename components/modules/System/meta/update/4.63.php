<?php
/**
 * @package   CleverStyle Framework
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

file_put_contents(
	DIR.'/.htaccess',
	str_replace(
	/** @lang ApacheConfig */
		<<<HTACCESS
<FilesMatch "\.(css|js|gif|jpg|jpeg|png|ico|svg|svgz|ttc|ttf|otf|woff|woff2|eot)$">
	RewriteEngine Off
</FilesMatch>
HTACCESS
		,
		/** @lang ApacheConfig */
		<<<HTACCESS
<FilesMatch "\.(css|js|gif|jpg|jpeg|png|ico|svg|svgz|ttc|ttf|otf|woff|woff2|eot)$">
	RewriteEngine Off
</FilesMatch>
<FilesMatch "\.(css|js|gif|jpg|jpeg|png|ico|svg|svgz|ttc|ttf|otf|woff|woff2|eot|html)$">
	<ifModule mod_expires.c>
		ExpiresActive On
		ExpiresDefault "access plus 1 month"
	</ifModule>
	<ifModule mod_headers.c>
		Header set Cache-Control "max-age=2592000, public"
	</ifModule>
</FilesMatch>
HTACCESS
		,
		file_get_contents(DIR.'/.htaccess')
	)
);
