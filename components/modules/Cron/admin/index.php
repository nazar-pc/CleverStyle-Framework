<?php
/**
 * @package   Cron
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;

if (isset($_POST['tasks'])) {
	$filename = TEMP.'/'.uniqid('cron', true);
	$tasks    = _trim(explode("\n", trim($_POST['tasks'])));
	$tasks    = implode("\n", $tasks);
	file_put_contents($filename, "$tasks\n");
	exec("crontab $filename", $result, $result);
	unlink($filename);
	Index::instance()->save($result === 0);
}

$L = Language::instance();
Page::instance()->content(
	h::{'form[is=cs-form]'}(
		h::{'p.cs-text-center'}(Language::instance()->crontab_content).
		h::{'textarea[is=cs-textarea][full-width][autosize][name=tasks][rows=10]'}(
			isset($_POST['tasks']) ? $_POST['tasks'] : shell_exec('crontab -l')
		).
		h::{'p button[is=cs-button][type=submit]'}(
			$L->save,
			[
				'tooltip' => $L->save_info
			]
		)
	)
);
