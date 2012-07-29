<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config;
$rc = $Config->routing['current'];
//If we provide some changes, but don't save them - we'll see popup window with proposition to save changes
//In case, when we agree with saving - $_POST['subpart'] contain name of previous subpart for providing correct saving
if (isset($rc[1]) || isset($_POST['subpart'])) {
	_include_once(MFOLDER.'/'.$rc[0].'/save.'.(isset($_POST['subpart']) ? $_POST['subpart'] : $rc[1]).'.php', false);
}