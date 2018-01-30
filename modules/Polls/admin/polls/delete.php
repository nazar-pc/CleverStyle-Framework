<?php
/**
 * @package  Polls
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Polls;
use
	cs\Language\Prefix,
	cs\Page,
	cs\Request,
	h;

$L    = new Prefix('polls_');
$poll = Polls::instance()->get(Request::instance()->route_ids[0]);
Page::instance()
	->title($L->deleting_of_poll($poll['title']))
	->content(
		h::{'cs-form form[action=admin/Polls/polls]'}(
			h::h2($L->sure_want_to_delete_poll($poll['title'])).
			h::p(
				h::{'cs-button button[type=submit][name=delete]'}(
					$L->yes,
					[
						'value' => $poll['id']
					]
				).
				h::{'cs-button button[type=button]'}(
					$L->cancel,
					[
						'onclick' => 'history.go(-1);'
					]
				)
			)
		)
	);
