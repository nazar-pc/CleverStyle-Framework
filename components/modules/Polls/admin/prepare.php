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
	cs\Index,
	cs\Language,
	cs\Page;

$Index             = Index::instance();
$Index->title_auto = false;
$Language = Language::instance();
$Page = Page::instance();
$Page->title(
	$Language->administration
);
$Page->title(
	$Language->Polls
);
