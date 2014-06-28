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
	cs\Index,
	cs\Language\Prefix,
	cs\User,
	h;

$Index               = Index::instance();
$L                   = new Prefix('polls_');
$poll                = Polls::instance()->get($Index->route_ids[0]);
$Options             = Options::instance();
$Index->action       = 'admin/Polls/polls';
$Index->apply_button = false;
$Index->content(
	h::{'p.cs-center'}($L->editing_of_poll($poll['title'])).
	h::{'table.cs-table tr'}(
		h::td([
			$L->poll_title,
			h::{'input[name=edit[title]]'}([
				'value' => $poll['title']
			])
		]),
		array_map(
			function ($option) {
				return h::{'td[colspan=2] input'}([
					'value' => $option['title'],
					'name'  => "edit[options][$option[id]]"
				]);
			},
			$Options->get($Options->get_all_for_poll($poll['id']))
		)
	).
	h::{'input[type=hidden][name=edit[id]]'}([
		'value' => $poll['id']
	])
);
