<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
file_put_contents(
	DIR.'/.htaccess',
	str_replace(
		[
			'
<Files license.txt>',
			'
<Files readme.html>
	RewriteEngine Off
</Files>
<Files favicon.ico>
	RewriteEngine Off
</Files>'
		],
		[
			'
<FilesMatch ".*/.*">
	Options -FollowSymLinks
</FilesMatch>
<FilesMatch "\.(css|js|gif|jpg|jpeg|png|ico|eot|ttc|ttf|svg|svgz|woff)$">
	RewriteEngine Off
</FilesMatch>
<Files license.txt>',
			''
		],
		file_get_contents(DIR.'/.htaccess')
	)
);
if (file_get_contents(DIR.'/custom.php') != '<?php
/**
 * Content of this file is not required!
 * You can add/edit/delete content as you want)
 * For example, you can add here including of class, which has the same name as core system class,
 * in this case your class will be used (may be useful in certain cases when modification of system files is needed)
 */
') {
	rename(DIR.'/custom.php', DIR.'/custom/custom.php');
} else {
	unlink(DIR.'/custom.php');
}
