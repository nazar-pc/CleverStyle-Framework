<?php
/**
 * @package		Cron
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Scheduled_tasks;
global $Index, $Page;
if (isset($_POST['edit_settings']) && $_POST['edit_settings'] == 'save') {
	$filename	= TEMP.'/'.uniqid('cron');
	$tasks		= $_POST['tasks'];
	if (!is_array($tasks)) {
		$tasks	= _trim(explode("\n", trim($tasks)));
	}
	$tasks		= implode("\n", $tasks)."\n";
	file_put_contents($filename, trim($tasks)."\n");
	exec('crontab '.$filename, $result, $result);
	unlink($filename);
	$Index->save($result === 0);
}
$Page->menumore		= \h::a(
	[
		'Crontab',
		[
			'href'	=> 'admin/'.MODULE,
			'class'	=> 'active'
		]
	]
);