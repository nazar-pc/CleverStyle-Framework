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
$Page	= Page::instance();
if (User::instance()->system()) {
	copy(
		$_POST['package'],
		$tmp_file = TEMP.'/'.md5($_POST['package'].MICROTIME).'.phar.php'
	);
	$tmp_dir		= 'phar://'.$tmp_file;
	$module			= file_get_contents($tmp_dir.'/dir');
	$fs				= file_get_json($tmp_dir.'/fs.json');
	$extract		= array_product(
		array_map(
			function ($index, $file) use ($tmp_dir, $module) {
				if (
					!file_exists(pathinfo(MODULES.'/'.$module.'/'.$file, PATHINFO_DIRNAME)) &&
					!mkdir(pathinfo(MODULES.'/'.$module.'/'.$file, PATHINFO_DIRNAME), 0700, true)
				) {
					return 0;
				}
				return (int)copy($tmp_dir.'/fs/'.$index, MODULES.'/'.$module.'/'.$file);
			},
			$fs,
			array_keys($fs)
		)
	);
	file_put_json(MODULES.'/'.$module.'/fs.json', array_keys($fs));
	$Page->content((int)(bool)$extract);
} else {
	$Page->content(0);
}
