<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
file_put_contents(TEMP.'/main.php', "<?php
\\cs\\Core::instance()->set('some_item', 'some value');");
$Core	= Core::instance();
return $Core->some_item === 'some value' ? 0 : 'Failed';
