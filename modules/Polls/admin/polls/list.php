<?php
/**
 * @package   Polls
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Polls;
use
	cs\Language\Prefix,
	cs\Page,
	h;

$L     = new Prefix('polls_');
$Polls = Polls::instance();
Page::instance()->content(
	h::{'cs-form form'}(
		h::{'table.cs-table[list]'}(
			h::{'tr th'}(
				$L->poll,
				$L->action
			).
			h::{'tr| td'}(
				array_map(
					function ($poll) use ($L) {
						return [
							$poll['title'],
							h::{'cs-link-button[icon=pencil]'}(
								h::a([
									'href'    => "admin/Polls/polls/edit/$poll[id]"
								]),
								[
									'tooltip' => $L->edit
								]
							).
							h::{'cs-link-button[icon=trash]'}(
								h::a([
									'href'    => "admin/Polls/polls/delete/$poll[id]"
								]),
								[
									'tooltip' => $L->delete
								]
							)
						];
					},
					$Polls->get($Polls->get_all())
				)
			)
		).
		h::h2($L->new_poll).
		h::{'p cs-input-text input[name=add[title]]'}(
			[
				'placeholder' => $L->poll_title
			]
		).
		h::{'p textarea[is=cs-textarea][autosize][name=add[options]]'}(
			[
				'placeholder' => $L->answers_one_per_line
			]
		).
		h::{'p cs-button button[type=submit]'}(
			$L->add
		)
	)
);
