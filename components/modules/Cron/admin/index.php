<?php
/**
 * @package		Cron
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Scheduled_tasks;
use			h;
global $Index, $L;
$Index->apply_button	= false;
$Index->content(
	h::{'p.cs-center'}($L->crontab_content).
	h::{'textarea.cs-wide-textarea[name=tasks]'}(
		isset($_POST['tasks']) ? $_POST['tasks'] : shell_exec('crontab -l')
	).
	h::br(2)
);