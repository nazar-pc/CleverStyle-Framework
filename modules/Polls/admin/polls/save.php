<?php
/**
 * @package  Polls
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Polls;

use
	cs\Language\Prefix,
	cs\Page;

$L       = new Prefix('polls_');
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
