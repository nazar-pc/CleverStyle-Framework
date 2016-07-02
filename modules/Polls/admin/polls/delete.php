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
	cs\Request,
	h;

$L    = new Prefix('polls_');
$poll = Polls::instance()->get(Request::instance()->route_ids[0]);
Page::instance()
	->title($L->deleting_of_poll($poll['title']))
	->content(
		h::{'form[is=cs-form][action=admin/Polls/polls]'}(
			h::h2($L->sure_want_to_delete_poll($poll['title'])).
			h::p(
				h::{'button[is=cs-button][type=submit][name=delete]'}(
					$L->yes,
					[
						'value' => $poll['id']
					]
				).
				h::{'button[is=cs-button][type=button]'}(
					$L->cancel,
					[
						'onclick' => 'history.go(-1);'
					]
				)
			)
		)
	);
