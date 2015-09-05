<?php
/**
 * @package   Cron
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;
$Index                        = Index::instance();
$Index->form_attributes['is'] = 'cs-button';
$Index->content(
	h::{'p.cs-text-center'}(Language::instance()->crontab_content).
	h::{'textarea[is=cs-textarea][full-width][autosize][name=tasks][rows=10]'}(
		isset($_POST['tasks']) ? $_POST['tasks'] : shell_exec('crontab -l')
	).
	h::br(2)
);
