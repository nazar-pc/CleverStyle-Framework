<?php
/**
 * @package   Polls
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
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
	h::{'form[is=cs-form]'}(
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
							h::{'a[is=cs-link-button][icon=pencil]'}(
								[
									'href'    => "admin/Polls/polls/edit/$poll[id]",
									'tooltip' => $L->edit
								]
							).
							h::{'a[is=cs-link-button][icon=trash]'}(
								[
									'href'    => "admin/Polls/polls/delete/$poll[id]",
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
		h::{'p input[is=cs-input-text][name=add[title]]'}(
			[
				'placeholder' => $L->poll_title
			]
		).
		h::{'p textarea[is=cs-textarea][autosize][name=add[options]]'}(
			[
				'placeholder' => $L->answers_one_per_line
			]
		).
		h::{'p button[is=cs-button][type=submit]'}(
			$L->add
		)
	)
);
