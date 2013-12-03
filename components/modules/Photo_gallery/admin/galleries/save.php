<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2013
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Photo_gallery;
use			cs\Config,
			cs\Index;
$Index			= Index::instance();
$Photo_gallery	= Photo_gallery::instance();
if (isset($_POST['add'])) {
	$add	= $_POST['add'];
	$Index->save($Photo_gallery->add_gallery($add['title'], isset($add['path']) ? $add['path'] : null, $add['description'], $add['active'], $add['preview_image']));
} elseif (isset($_POST['edit'])) {
	$edit	= $_POST['edit'];
	$Index->save($Photo_gallery->set_gallery($edit['id'], $edit['title'], isset($edit['path']) ? $edit['path'] : null, $edit['description'], $edit['active'], $edit['preview_image']));
} elseif (isset($_POST['delete'])) {
	$Index->save($Photo_gallery->del_gallery($_POST['delete']));
}