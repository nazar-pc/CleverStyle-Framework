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
$L			= Language::instance();
$Storage	= Storage::instance();
if (isset($_GET['index'])) {
	$result	= $Storage->test([$_GET['index']]);
} else {
	$result	= $Storage->test($_GET['storage']);
}
Page::instance()->json(
	h::{'p.cs-test-result'}($result ? $L->success : $L->failed)
);
