<?php
/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	Karwana\Mime\Mime;

$L       = new Language\Prefix('uploader_');
$Page    = Page::instance();
$Request = Request::instance();
$User    = User::instance();
/**
 * File should be specified
 */
$file = $Request->files('file');
if (!$file) {
	throw new ExitException('File Not Specified', 400);
}
/**
 * Getting Uploader module configuration
 */
$module_data   = Config::instance()->module('Uploader');
$max_file_size = (int)$module_data->max_file_size;
switch (substr(strtolower($module_data->max_file_size), -2)) {
	case 'gb':
		$max_file_size *= 1024;
	case 'mb':
		$max_file_size *= 1024;
	case 'kb':
		$max_file_size *= 1024;
}
if ($file['error'] & (UPLOAD_ERR_INI_SIZE | UPLOAD_ERR_FORM_SIZE) || $file['size'] > $max_file_size) {
	throw new ExitException(
		$L->file_too_large(
			format_filesize($max_file_size)
		),
		400
	);
}
unset($max_file_size);
if ($file['error'] != UPLOAD_ERR_OK) {
	throw new ExitException(500);
}
/**
 * Only registered users allowed
 */
if (!$User->user()) {
	throw new ExitException(403);
}
/**
 * Getting instances of storage and database
 */
$storage = Storage::instance()->storage($module_data->storage('files'));
/**
 * @var DB\_Abstract $cdb
 */
$cdb = DB::instance()->db_prime($module_data->db('files'));
if (!$storage || !$cdb) {
	throw new ExitException(500);
}
/**
 * Moving file into storage
 */
if (!$module_data->directory_created) {
	$storage->mkdir('Uploader');
	$module_data->directory_created = 1;
}
$destination_file = 'Uploader/'.date('Y-m-d');
if (!$storage->file_exists($destination_file)) {
	$storage->mkdir($destination_file);
}
$destination_file .= date('/H');
if (!$storage->file_exists($destination_file)) {
	$storage->mkdir($destination_file);
}
$destination_file .= "/$User->id".uniqid(date('_is'), true);
require_once __DIR__.'/../Mime/Mime.php';
$destination_file .= '.'.Mime::guessExtension($file['tmp_name'], $file['name']);
if (!$storage->copy($file['tmp_name'], $destination_file)) {
	throw new ExitException(500);
}
/**
 * Registering file in database
 */
if (!$cdb->q(
	"INSERT INTO `[prefix]uploader_files`
		(`user`, `uploaded`, `source`, `url`)
	VALUES
		('%s', '%s', '%s', '%s')",
	$User->id,
	TIME,
	$destination_file,
	$url = $storage->url_by_source($destination_file)
)
) {
	$storage->unlink($destination_file);
	throw new ExitException(500);
}
if ($cdb->id() % 100 === 0) {
	$files_for_deletion = $cdb->qfa(
		"SELECT `f`.`id`, `f`.`source`
		FROM `[prefix]uploader_files` AS `f`
		LEFT JOIN `[prefix]uploader_files_tags` AS `t`
		ON `f`.`id` = `t`.`id`
		WHERE
			`f`.`uploaded`	< '%s' AND
			`t`.`id` IS NULL
		ORDER BY `f`.`id` ASC
		LIMIT 100",
		time() - $module_data->confirmation_time
	);
	if ($files_for_deletion) {
		$ids = implode(',', array_column($files_for_deletion, 'id'));
		$cdb->q(
			[
				"DELETE FROM `[prefix]uploader_files`
				WHERE `id` IN($ids)",
				"DELETE FROM `[prefix]uploader_files_tags`
				WHERE `id` IN($ids)"
			]
		);
		unset($ids);
	}
	$files_for_deletion = array_column($files_for_deletion, 'source');
	foreach ($files_for_deletion as $source) {
		if ($storage->file_exists($source)) {
			$storage->unlink($source);
		}
	}
	unset($files_for_deletion, $source);
}
$Page->json(['url' => $url]);
