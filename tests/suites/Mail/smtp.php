<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2011—2013
 * @license		MIT License, see license.txt
 */
return !file_exists(DIR.'/core/classes/thirdparty/SMTP.php') ? 'File with SMTP class for PHPMailer should be named "SMTP.php"' : 0;