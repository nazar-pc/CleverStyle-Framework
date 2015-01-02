<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
if (!isset($_POST['mode'])) {
	return;
}
$Config	= Config::instance();
$update	= false;
if ($_POST['mode'] == 'add') {
	foreach ($_POST['storage'] as $item => $value) {
		$_POST['storage'][$item] = $value;
	}
	$Config->storage[] = $_POST['storage'];
	unset($item, $value);
	$update = true;
} elseif ($_POST['mode'] == 'edit' && $_POST['storage_id'] > 0) {
	$cstorage = &$Config->storage[$_POST['storage_id']];
	foreach ($_POST['storage'] as $item => $value) {
		$cstorage[$item] = $value;
	}
	unset($cstorage, $item, $value);
	$update = true;
} elseif ($_POST['mode'] == 'delete' && isset($_POST['storage']) && $_POST['storage'] > 0) {
	unset($Config->storage[$_POST['storage']]);
	$update = true;
}
if ($update) {
	Index::instance()->save();
}
unset($update);
