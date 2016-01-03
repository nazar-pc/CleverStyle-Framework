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
	cs\Route,
	h;

$L    = new Prefix('polls_');
$poll = Polls::instance()->get(Route::instance()->ids[0]);
Page::instance()
	->title($L->deleting_of_poll($poll['title']))
	->content(
		h::{'form[is=cs-form][action=admin/Polls/polls]'}(
			h::h2($L->deleting_of_poll($poll['title'])).
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
