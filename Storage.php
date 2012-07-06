<?php
header('Content-Type: text/html; charset=utf-8');
header("Connection: close");
chdir(__DIR__);
require __DIR__.'/core/functions.php';
$DOMAIN = str_replace(
	['/', '\\'],
	'',
	(string)$_POST['domain']
);
$DOMAIN = null_byte_filter($DOMAIN);
define('STORAGE',	__DIR__.'/storages/'.$DOMAIN.'/public');	//For storage on the same server as site or
																	//if there are several storages for several domains
																	//on one server
//define('STORAGE',	__DIR__);										//For storage on separate server
if (
	$_SERVER['HTTP_USER_AGENT'] == 'CleverStyle CMS' &&
	strpos($DOMAIN, '\\') === false &&
	strpos($DOMAIN, '/') === false &&
	file_exists(__DIR__.'/storages/'.$DOMAIN.'/config.php') &&
	isset($_POST['data'])
) {
	include __DIR__.'/storages/'.$DOMAIN.'/config.php';
	global $STORAGE_USER, $STORAGE_PASSWORD;
	$data = _json_decode(urldecode($_POST['data']));
	$KEY = substr($data['key'], 0, 32);
	unset($data['key']);
	if (md5(_json_encode($data).$STORAGE_USER.$STORAGE_PASSWORD) !== $KEY) {
		exit;
	}
	unset($GLOBALS['STORAGE_USER'], $GLOBALS['STORAGE_PASSWORD'], $KEY, $DOMAIN);
} else {
	exit;
}

switch ($data['function']) {
	default:
		exit;
	case 'get_list':
		exit(_json_encode(get_list(STORAGE.'/'.$data['dir'], $data['mask'], $data['mode'], $data['with_path'], $data['subfolders'], $data['sort'], $data['exclusion'])));
	case 'file_get_contents':
		exit(file_get_contents(STORAGE.'/'.$data['filename'], $data['flags'], null, $data['offset'], $data['maxlen']));
	case 'file_put_contents':
		exit(file_put_contents(STORAGE.'/'.$data['filename'], $data['data'], $data['flags']));
	case 'copy':
		exit(copy($data['http'] ? $data['source'] : STORAGE.'/'.$data['source'], STORAGE.'/'.$data['dest']));
	case 'unlink':
		exit(unlink(STORAGE.'/'.$data['filename']));
	case 'file_exists':
		exit(file_exists(STORAGE.'/'.$data['filename']));
	case 'move_uploaded_file':
		exit(copy($data['filename'], STORAGE.'/'.$data['destination']));
	case 'rename':
		exit($data['http'] ? copy($data['oldname'], STORAGE.'/'.$data['newname']) : rename(STORAGE.'/'.$data['oldname'], STORAGE.'/'.$data['newname']));
	case 'mkdir':
		exit(mkdir(STORAGE.'/'.$data['pathname']));
	case 'rmdir':
		exit(rmdir(STORAGE.'/'.$data['dirname']));
	case 'is_file':
		exit(is_file(STORAGE.'/'.$data['filename']));
	case 'is_dir':
		exit(is_dir(STORAGE.'/'.$data['filename']));
	case 'test':
		exit('OK');
}