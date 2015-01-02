<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
file_put_contents(DIR.'/storage/.htaccess', "Deny from all\nRewriteEngine Off\n<Files *>\n\tSetHandler default-handler\n</Files>");
