<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
use h;
$db	= DB::instance();
$L	= Language::instance();
if (isset($_GET['mirror_index'])) {
	$result	= $db->test([$_GET['index'], $_GET['mirror_index']]);
} elseif (isset($_GET['index'])) {
	$result	= $db->test([$_GET['index']]);
} else {
	$result	= $db->test($_GET['db']);
}
Page::instance()->json(
	h::{'p.cs-test-result'}($result ? $L->success : $L->failed)
);
