<?php
/**
 * @package        Polls
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Polls;

use
	cs\Config,
	cs\Index;

$Index   = Index::instance();
$Options = Options::instance();
$Polls   = Polls::instance();
if (isset($_POST['edit'])) {
	$edit = $_POST['edit'];
	$Polls->set($edit['id'], $edit['title']);
	foreach ($edit['options'] as $id => $title) {
		$Options->set($id, $edit['id'], $title);
	}
	$Index->save(true);
} elseif (isset($_POST['add'])) {
	$add  = $_POST['add'];
	$poll = $Polls->add($add['title']);
	if (!$poll) {
		$Index->save(false);
		return;
	}
	foreach (explode("\n", trim($add['options'])) as $title) {
		$Options->add($poll, trim($title));
	}
	$Index->save(true);
}
