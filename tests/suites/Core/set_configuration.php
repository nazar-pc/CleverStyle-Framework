<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2011—2013
 * @license		MIT License, see license.txt
 */
namespace	cs;
file_put_contents(TEMP.'/main.php', "<?php
define('FIXED_LANGUAGE',	false);
\\cs\\Core::instance()->set('some_item', 'some value');");
$Core	= Core::instance();
return defined('FIXED_LANGUAGE') && $Core->some_item === 'some value' ? 0 : 'Failed';