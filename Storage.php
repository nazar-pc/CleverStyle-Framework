<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	HTTP Storage Engine backend
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 *
 * Requires config.php file in current directory with specified global variables $STORAGE_USER and $STORAGE_PASSWORD
 */
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
require __DIR__.'/core/functions.php';
define('STORAGE',	__DIR__.'/storage/public');
chdir(STORAGE);
if (
	$_SERVER['HTTP_USER_AGENT'] == 'CleverStyle CMS' &&
	file_exists(__DIR__.'/config.php') &&
	isset($_POST['data'])
) {
	include __DIR__.'/config.php';
	global $STORAGE_USER, $STORAGE_PASSWORD;
	$data = _json_decode(urldecode($_POST['data']));
	$KEY = substr($data['key'], 0, 32);
	unset($data['key']);
	if (md5(_json_encode($data).$STORAGE_USER.$STORAGE_PASSWORD) !== $KEY) {
		exit;
	}
	unset($GLOBALS['STORAGE_USER'], $GLOBALS['STORAGE_PASSWORD'], $KEY);
} else {
	exit;
}

switch ($data['function']) {
	default:
		exit;
	case 'get_files_list':
		exit(_json_encode(get_files_list($data['dir'], $data['mask'], $data['mode'], $data['prefix_path'], $data['subfolders'], $data['sort'], $data['exclusion'], $data['system_files'])));
	case 'file':
		exit(_json_encode(file($data['filename'], $data['flags'])));
	case 'file_get_contents':
		exit(file_get_contents($data['filename'], $data['flags'], null, $data['offset'], $data['maxlen']));
	case 'file_put_contents':
		exit(file_put_contents($data['filename'], $data['data'], $data['flags']));
	case 'copy':
		exit(copy($data['source'], $data['dest']));
	case 'unlink':
		exit(unlink($data['filename']));
	case 'file_exists':
		exit(file_exists($data['filename']));
	case 'move_uploaded_file':
		exit(copy($data['filename'], $data['destination']));
	case 'rename':
		exit(rename($data['oldname'], $data['newname']));
	case 'mkdir':
		exit(mkdir($data['pathname']));
	case 'rmdir':
		exit(rmdir($data['dirname']));
	case 'is_file':
		exit(is_file($data['filename']));
	case 'is_dir':
		exit(is_dir($data['filename']));
	case 'test':
		exit('OK');
}