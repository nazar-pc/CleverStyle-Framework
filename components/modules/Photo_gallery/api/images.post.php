<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Photo_gallery;
use
	cs\Config,
	cs\Page,
	cs\User;
if (!User::instance()->user()) {
	error_code(403);
	return;
}
if (!isset($_POST['files'], $_POST['gallery']) || empty($_POST['files'])) {
	error_code(400);
	return;
}
$Photo_gallery	= Photo_gallery::instance();
$files			= $_POST['files'];
foreach ($files as $i => &$file) {
	$file	= $Photo_gallery->add($file, $_POST['gallery']);
	if (!$file) {
		unset($files[$i]);
	}
}
unset($i, $file);
Page::instance()->json($files);
