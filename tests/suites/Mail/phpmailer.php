<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
require	DIR.'/core/classes/thirdparty/PHPMailer.php';
return method_exists('PHPMailer', '__construct') ? '__construct() method should be removed from PHPMailer class' : 0;