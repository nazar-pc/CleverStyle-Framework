<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
if (!isset($_POST['mode'])) {
	return;
}
$Index	= Index::instance();
$Config	= Config::instance();
$update = false;
if ($_POST['mode'] == 'add') {
	foreach ($_POST['db'] as $item => $value) {
		$_POST['db'][$item] = $value;
	}
	unset($item, $value);
	$_POST['db']['mirrors'] = [];
	if ($_POST['db']['mirror'] == -1) {
		$Config->db[] = $_POST['db'];
	} else {
		$Config->db[$_POST['db']['mirror']]['mirrors'][] = $_POST['db'];
	}
	$update = true;
} elseif ($_POST['mode'] == 'edit') {
	if (isset($_POST['mirror'])) {
		$cdb = &$Config->db[$_POST['database']]['mirrors'][$_POST['mirror']];
	} elseif ($_POST['database'] > 0) {
		$cdb = &$Config->db[$_POST['database']];
	}
	foreach ($_POST['db'] as $item => $value) {
		$cdb[$item] = $value;
	}
	unset($cdb, $item, $value);
	$update = true;
} elseif ($_POST['mode'] == 'delete' && isset($_POST['database'])) {
	if (isset($_POST['mirror'])) {
		unset($Config->db[$_POST['database']]['mirrors'][$_POST['mirror']]);
		$update = true;
	} elseif ($_POST['database'] > 0) {
		unset($Config->db[$_POST['database']]);
		$update = true;
	}
} elseif ($_POST['mode'] == 'config') {
	_include_once(__DIR__."/../save.php", false);
}
if ($update) {
	$Index->save();
}
unset($update);
