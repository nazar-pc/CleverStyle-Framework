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
$Options             = Options::instance();
$Page->title(
	$L->editing_of_poll($poll['title'])
);
$Index->action				= 'admin/Polls/polls';
$Index->apply_button		= false;
$Index->cancel_button_back	=  true;
$Index->content(
	h::{'h2.cs-center'}($L->editing_of_poll($poll['title'])).
	h::{'cs-table[right-left] cs-table-row cs_table_cell'}(
		$L->poll_title,
		h::{'input[name=edit[title]]'}([
			'value' => $poll['title']
		])
	).
	h::p(array_map(
		function ($option) {
			return h::{'input'}([
				'value' => $option['title'],
				'name'  => "edit[options][$option[id]]"
			]);
		},
		$Options->get($Options->get_all_for_poll($poll['id']))
	)).
	h::{'input[type=hidden][name=edit[id]]'}([
		'value' => $poll['id']
	])
);
