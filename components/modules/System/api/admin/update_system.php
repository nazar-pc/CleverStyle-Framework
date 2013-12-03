<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Page	= Page::instance();
if (User::instance()->system()) {
	/**
	 * Extracting new versions of files
	 */
	copy(
		$_POST['package'],
		$tmp_file = TEMP.'/'.md5($_POST['package'].MICROTIME).'.phar.php'
	);
	$tmp_dir	= "phar://$tmp_file";
	$module_dir	= MODULES.'/System';
	if (file_exists("$module_dir/fs_old.json")) {
		$Page->content(1);
	}
	copy(DIR.'/core/fs.json',		DIR.'/core/fs_old.json');
	$fs			= _json_decode(file_get_contents("$tmp_dir/fs.json"))['core/fs.json'];
	$fs			= _json_decode(file_get_contents("$tmp_dir/fs/$fs"));
	$extract	= array_product(
		array_map(
			function ($index, $file) use ($tmp_dir, $module_dir) {
				if (
					!file_exists(pathinfo(DIR."/$file", PATHINFO_DIRNAME)) &&
					!mkdir(pathinfo(DIR."/$file", PATHINFO_DIRNAME), 0700, true)
				) {
					return 0;
				}
				return (int)copy("$tmp_dir/fs/$index", DIR."/$file");
			},
			$fs,
			array_keys($fs)
		)
	);
	file_put_contents(DIR.'/core/fs.json', _json_encode($fs = array_keys($fs)));
	/**
	 * Removing of old unnecessary files and directories
	 */
	foreach (array_diff(_json_decode(file_get_contents(DIR.'/core/fs_old.json')), $fs) as $file) {
		$file	= DIR."/$file";
		if (file_exists($file) && is_writable($file)) {
			unlink($file);
			if (!get_files_list($dir = pathinfo($file, PATHINFO_DIRNAME))) {
				rmdir($dir);
			}
		}
	}
	unset($fs, $file, $dir);
	$Page->content((int)(bool)$extract);
} else {
	$Page->content(0);
}