<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     System module
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
file_put_contents(
	PCACHE.'/.htaccess',
	'<FilesMatch "\.(css|js|html)$">
	Allow From All
</FilesMatch>
<ifModule mod_expires.c>
	ExpiresActive On
	ExpiresDefault "access plus 1 month"
</ifModule>
<ifModule mod_headers.c>
	Header set Cache-Control "max-age=2592000, public"
</ifModule>
AddEncoding gzip .js
AddEncoding gzip .css
AddEncoding gzip .html
'
);
