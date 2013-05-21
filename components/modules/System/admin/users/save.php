<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config;
$rc = $Config->route;
if (isset($rc[1])) {
	_include_once(MFOLDER.'/'.$rc[0].'/save.'.$rc[1].'.php', false);
}