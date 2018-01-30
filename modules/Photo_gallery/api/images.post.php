<?php
/**
 * @package  Photo gallery
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Photo_gallery;
use
	cs\ExitException,
	cs\Page,
	cs\User;
if (!User::instance()->user()) {
	throw new ExitException(403);
}
if (!isset($_POST['files'], $_POST['gallery']) || empty($_POST['files'])) {
	throw new ExitException(400);
}
$Photo_gallery = Photo_gallery::instance();
$files         = $_POST['files'];
foreach ($files as $i => &$file) {
	$file = $Photo_gallery->add($file, $_POST['gallery']);
	if (!$file) {
		unset($files[$i]);
	}
}
unset($i, $file);
Page::instance()->json($files);
