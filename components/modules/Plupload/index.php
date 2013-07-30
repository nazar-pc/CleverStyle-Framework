<?php
/**
 * @package		Plupload
 * @category	modules
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU GPL v2, see license.txt
 */
namespace	cs;
$Page				= Page::instance();
$User				= User::instance();
interface_off();
/**
 * Only registered users allowed
 */
if (!isset($_FILES['file'])) {
	$Page->json([
		'jsonrpc'	=> '2.0',
		'error'		=> [
			'code'		=> 500,
			'message'	=> '500 Internal Server Error'
		],
		'id'		=> 'id'
	]);
	return;
}
/**
 * Getting Plupload module configuration
 */
$module_data		= Config::instance()->module('Plupload');
$max_file_size		= trim(strtolower($module_data->max_file_size), 'b');
switch (substr($max_file_size, -1)) {
	case 'k':
		$max_file_size	= substr($max_file_size, 0, -1) * 1024;
	break;
	case 'm':
		$max_file_size	= substr($max_file_size, 0, -1) * 1024 * 1024;
	break;
	case 'g':
		$max_file_size	= substr($max_file_size, 0, -1) * 1024 * 1024 * 1024;
	break;
	default:
		$max_file_size	= substr($max_file_size, 0, -1);
}
if ($_FILES['file']['error'] & (UPLOAD_ERR_INI_SIZE | UPLOAD_ERR_FORM_SIZE) || $_FILES['file']['size'] > $max_file_size) {
	$Page->json([
		'jsonrpc'	=> '2.0',
		'error'		=> [
			'code'		=> 400,
			'message'	=> '400 File too large'
		],
		'id'		=> 'id'
	]);
	return;
}
unset($max_file_size);
if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
	$Page->json([
		'jsonrpc'	=> '2.0',
		'error'		=> [
			'code'		=> 500,
			'message'	=> '500 Internal Server Error'
		],
		'id'		=> 'id'
	]);
	return;
}
if (!$User->user()) {
	$Page->json([
		'jsonrpc'	=> '2.0',
		'error'		=> [
			'code'		=> 403,
			'message'	=> '403 Forbidden'
		],
		'id'		=> 'id'
	]);
	return;
}
/**
 * Getting instances of storage and database
 */
$storage			= Storage::instance()->{$module_data->storage('files')};
$cdb				= DB::instance()->{$module_data->db('files')}();
if (!$storage || !$cdb) {
	$Page->json([
		'jsonrpc'	=> '2.0',
		'error'		=> [
			'code'		=> 500,
			'message'	=> '500 Internal Server Error'
		],
		'id'		=> 'id'
	]);
	return;
}
/**
 * Moving file into storage
 */
if (!$module_data->directory_created) {
	$storage->mkdir('Plupload');
	$module_data->directory_created	= 1;
}
$destination_file	= 'Plupload/'.date('Y-m-d');
if (!$storage->file_exists($destination_file)) {
	$storage->mkdir($destination_file);
}
$destination_file	.= date('/H');
if (!$storage->file_exists($destination_file)) {
	$storage->mkdir($destination_file);
}
$destination_file	.= '/'.$User->id.date('_i:s_').uniqid();
if (!$storage->copy($_FILES['file']['tmp_name'], $destination_file)) {
	$Page->json([
		'jsonrpc'	=> '2.0',
		'error'		=> [
			'code'		=> 500,
			'message'	=> '500 Internal Server Error'
		],
		'id'		=> 'id'
	]);
	return;
}
/**
 * Registering file in database
 */
if (!$cdb->q(
	"INSERT INTO `[prefix]plupload_files`
		(`user`, `uploaded`, `source`, `url`)
	VALUES
		('%s', '%s', '%s', '%s')",
	$User->id,
	TIME,
	$destination_file,
	$url = $storage->url_by_source($destination_file)
)) {
	$storage->unlink($destination_file);
	$Page->json([
		'jsonrpc'	=> '2.0',
		'error'		=> [
			'code'		=> 500,
			'message'	=> '500 Internal Server Error'
		],
		'id'		=> 'id'
	]);
	return;
}
if ($cdb->id() % 100 === 0) {
	$files_for_deletion	= $cdb->qfa([
		"SELECT `f`.`id`, `f`.`source`
		FROM `[prefix]plupload_files` AS `f`
		LEFT JOIN `[prefix]plupload_files_tags` AS `t`
		ON `f`.`id` = `t`.`id`
		WHERE
			`f`.`uploaded`	< '%s' AND
			`t`.`id` IS NULL
		ORDER BY `f`.`id` ASC
		LIMIT 100",
		time() - $module_data->confirmation_time
	]);
	if ($files_for_deletion) {
		$ids	= implode(',', array_column($files_for_deletion, 'id'));
		$cdb->q([
			"DELETE FROM `[prefix]plupload_files`
			WHERE `id` IN($ids)",
			"DELETE FROM `[prefix]plupload_files_tags`
			WHERE `id` IN($ids)"
		]);
		unset($ids);
	}
	$files_for_deletion	= array_column($files_for_deletion, 'source');
	foreach ($files_for_deletion as $source) {
		if ($storage->file_exists($source)) {
			$storage->unlink($source);
		}
	}
	unset($files_for_deletion, $source);
}
$Page->json([
	'jsonrpc'	=> '2.0',
	'result'	=> $url,
	'id'		=> 'id'
]);