<?php
/**
 * @package		Cron
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Index					= Index::instance();
$Index->apply_button	= false;
$Index->content(
	h::{'p.cs-center'}(Language::instance()->crontab_content).
	h::{'textarea[name=tasks]'}(
		isset($_POST['tasks']) ? $_POST['tasks'] : shell_exec('crontab -l')
	).
	h::br(2)
);
