<?php
/**
 * @package   Cron
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h,
	cs\Language\Prefix;

$L    = new Prefix('system_admin_');
$Page = Page::instance();
if (isset($_POST['tasks'])) {
	$filename = TEMP.'/'.uniqid('cron', true);
	$tasks    = _trim(explode("\n", trim($_POST['tasks'])));
	$tasks    = implode("\n", $tasks);
	file_put_contents($filename, "$tasks\n");
	exec("crontab $filename", $result, $result);
	unlink($filename);
	if ($result === 0) {
		$Page->success($L->changes_saved);
	} else {
		$Page->warning($L->changes_save_error);
	}
}

$Page->content(
	h::{'form[is=cs-form]'}(
		h::{'p.cs-text-center'}(Language::instance()->crontab_content).
		h::{'textarea[is=cs-textarea][full-width][autosize][name=tasks][rows=10]'}(
			isset($_POST['tasks']) ? $_POST['tasks'] : shell_exec('crontab -l')
		).
		h::{'p cs-button'}(
			h::{'button[type=submit]'}($L->save),
			[
				'tooltip' => $L->save_info
			]
		)
	)
);
