<?php
/**
 * @package    CleverStyle CMS
 * @subpackage HTTP Storage Engine backend
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 *
 * Requires config.php file in current directory with specified global variables $STORAGE_USER and $STORAGE_PASSWORD
 */
header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
require __DIR__.'/core/thirdparty/upf.php';
chdir(__DIR__.'/storage/public');
if (
	isset($_SERVER['HTTP_USER_AGENT'], $_POST['data']) &&
	$_SERVER['HTTP_USER_AGENT'] == 'CleverStyle CMS' &&
	file_exists(__DIR__.'/config.php')
) {
	list($STORAGE_USER, $STORAGE_PASSWORD) = __DIR__.'/config.php';
	$data = _json_decode(urldecode($_POST['data']));
	$KEY  = substr($data['key'], 0, 32);
	unset($data['key']);
	if (md5(_json_encode($data).$STORAGE_USER.$STORAGE_PASSWORD) !== $KEY) {
		return;
	}
} else {
	return;
}

switch ($data['function']) {
	default:
		return;
	case 'get_files_list':
		echo _json_encode(get_files_list($data['dir'], $data['mask'], $data['mode'], $data['prefix_path'], $data['subfolders'], $data['sort'], $data['exclusion'], $data['system_files'], null, $data['limit']));
		return;
	case 'file':
		echo _json_encode(file($data['filename'], $data['flags']));
		return;
	case 'file_get_contents':
		echo file_get_contents($data['filename'], $data['flags'], null, $data['offset'], $data['maxlen']);
		return;
	case 'file_put_contents':
		echo file_put_contents($data['filename'], $data['data'], $data['flags']);
		return;
	case 'copy':
		echo copy($data['source'], $data['dest']);
		return;
	case 'unlink':
		echo unlink($data['filename']);
		return;
	case 'file_exists':
		echo file_exists($data['filename']);
		return;
	case 'move_uploaded_file':
		echo copy($data['filename'], $data['destination']);
		return;
	case 'rename':
		echo rename($data['oldname'], $data['newname']);
		return;
	case 'mkdir':
		echo mkdir($data['pathname']);
		return;
	case 'rmdir':
		echo rmdir($data['dirname']);
		return;
	case 'is_file':
		echo is_file($data['filename']);
		return;
	case 'is_dir':
		echo is_dir($data['filename']);
		return;
	case 'test':
		echo 'OK';
		return;
}
