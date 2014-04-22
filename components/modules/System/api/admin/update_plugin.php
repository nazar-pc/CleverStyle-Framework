<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	module
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
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
	$plugin		= file_get_contents("$tmp_dir/dir");
	$plugin_dir	= PLUGINS."/$plugin";
	if (file_exists("$plugin_dir/fs_old.json")) {
		$Page->content(1);
	}
	copy("$plugin_dir/fs.json",		"$plugin_dir/fs_old.json");
	$fs			= file_get_json("$tmp_dir/fs.json");
	$extract	= array_product(
		array_map(
			function ($index, $file) use ($tmp_dir, $plugin_dir) {
				if (
					!file_exists(dirname("$plugin_dir/$file")) &&
					!mkdir(dirname("$plugin_dir/$file"), 0700, true)
				) {
					return 0;
				}
				return (int)copy("$tmp_dir/fs/$index", "$plugin_dir/$file");
			},
			$fs,
			array_keys($fs)
		)
	);
	file_put_json("$plugin_dir/fs.json", $fs = array_keys($fs));
	/**
	 * Removing of old unnecessary files and directories
	 */
	foreach (array_diff(file_get_json("$plugin_dir/fs_old.json"), $fs) as $file) {
		$file	= "$plugin_dir/$file";
		if (file_exists($file) && is_writable($file)) {
			unlink($file);
			if (!get_files_list($dir = dirname($file))) {
				rmdir($dir);
			}
		}
	}
	unset($fs, $file, $dir);
	unlink("$plugin_dir/fs_old.json");
	$Page->content((int)(bool)$extract);
} else {
	$Page->content(0);
}
