<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	module
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $Cache, $User, $Page, $L;
if ($User->system()) {
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
	$fs			= _json_decode(file_get_contents("$tmp_dir/fs.json"));
	$extract	= array_product(
		array_map(
			function ($index, $file) use ($tmp_dir, $plugin_dir) {
				if (
					!file_exists(pathinfo("$plugin_dir/$file", PATHINFO_DIRNAME)) &&
					!mkdir(pathinfo("$plugin_dir/$file", PATHINFO_DIRNAME), 0700, true)
				) {
					return 0;
				}
				return (int)copy("$tmp_dir/fs/$index", "$plugin_dir/$file");
			},
			array_keys($fs),
			$fs
		)
	);
	file_put_contents(PLUGINS.'/'.$plugin.'/fs.json', _json_encode($fs = array_values($fs)));
	/**
	 * Removing of old unnecessary files and directories
	 */
	foreach (array_diff(_json_encode($plugin_dir.'/fs_old.json'), $fs) as $file) {
		$file	= "$plugin_dir/$file";
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