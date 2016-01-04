<?php
/**
 * @package        Polls
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Polls;

use
	cs\Language,
	cs\Page;

$L       = Language::instance();
$Page    = Page::instance();
$Options = Options::instance();
$Polls   = Polls::instance();
if (isset($_POST['add'])) {
	$add  = $_POST['add'];
	$poll = $Polls->add($add['title']);
	if (!$poll) {
		$Page->warning($L->changes_save_error);
		return;
	}
	foreach (explode("\n", trim($add['options'])) as $title) {
		$Options->add($poll, trim($title));
	}
	$Page->success($L->changes_saved);
} elseif (isset($_POST['edit'])) {
	$edit = $_POST['edit'];
	$Polls->set($edit['id'], $edit['title']);
	foreach ($edit['options'] as $id => $title) {
		$Options->set($id, $edit['id'], $title);
	}
	$Page->success($L->changes_saved);
} elseif (isset($_POST['delete'])) {
	if ($Polls->del($_POST['delete'])) {
		$Page->success($L->changes_saved);
	} else {
		$Page->warning($L->changes_save_error);
	}
}
