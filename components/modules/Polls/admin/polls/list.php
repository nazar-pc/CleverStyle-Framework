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
$Index			= Index::instance();
$L				= new Prefix('polls_');
$Polls			= Polls::instance();
$Index->buttons	= false;
$Index->content(
	h::{'cs-table[list][with-header] cs-table-row| cs-table-cell'}(
		[
			$L->poll,
			$L->action
		],
		array_map(
			function ($poll) use ($Index, $L) {
				return [
					$poll['title'],
					h::{'a.uk-button'}(
						$L->edit,
						[
							'href' => "admin/Polls/polls/edit/$poll[id]"
						]
					).
					h::{'a.uk-button'}(
						$L->delete,
						[
							'href' => "admin/Polls/polls/delete/$poll[id]"
						]
					)
				];
			},
			$Polls->get($Polls->get_all())
		)
	).
	h::{'h2.cs-center'}($L->new_poll).
	h::{'p input[name=add[title]]'}([
		'placeholder' => $L->poll_title
	]).
	h::{'p textarea[name=add[options]]'}([
		'placeholder' => $L->answers_one_per_line
	])
);
$Index->custom_buttons = h::{'button.uk-button[type=submit]'}($L->add);
