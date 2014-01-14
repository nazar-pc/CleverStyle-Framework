<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  System/robots.txt<br>
 *  ['text'	=> <i>&$text</i>]<br>
 */
namespace	cs;
Index::instance()->stop		= true;
interface_off();
$text						= file_get_contents(MFOLDER.'/robots.txt');
Trigger::instance()->run(
	'System/robots.txt',
	[
		'text'	=> &$text
	]
);
$text						.= 'Host: '.explode(
	'/',
	explode('//', Config::instance()->core_url(), 2)[1],
	2
)[0];
Page::instance()->Content	= $text;
