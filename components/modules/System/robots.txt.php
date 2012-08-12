<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  System/robots.txt<br>
 *  ['text'	=> <i>&$text</i>]<br>
 */
global $Core, $Config, $Index, $Page;
$Index->stop	= true;
interface_off();
$text			= file_get_contents(MFOLDER.'/robots.txt');
$Core->run_trigger(
	'System/robots.txt',
	[
		'text'	=> &$text
	]
);
$text			.= 'Host: '.explode(
	'/',
	explode('//', $Config->core['url'])[1]
)[0];
$Page->Content	= $text;