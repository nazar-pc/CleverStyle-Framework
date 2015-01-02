<?php
/**
 * @package        Polls
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Polls;

use
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page,
	cs\User,
	h;

$Index               = Index::instance();
$Page                = Page::instance();
$L                   = new Prefix('polls_');
$poll                = Polls::instance()->get($Index->route_ids[0]);
$Page->title(
	$L->deleting_of_poll($poll['title'])
);
$Index->action       = 'admin/Polls/polls';
$Index->apply_button = false;
$Index->content(
	h::{'h2.cs-center'}($L->deleting_of_poll($poll['title'])).
	h::{'button.uk-button[type=submit][name=delete]'}(
		$L->yes,
		[
			'value'	=> $poll['id']
		]
	)
);
