<?php
/**
 * @package   Polls
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Polls;

use
	cs\Index,
	cs\Language\Prefix,
	cs\Page,
	cs\Route,
	h;

$Index   = Index::instance();
$Page    = Page::instance();
$L       = new Prefix('polls_');
$poll    = Polls::instance()->get(Route::instance()->ids[0]);
$Options = Options::instance();
$Page->title(
	$L->editing_of_poll($poll['title'])
);
$Index->action             = 'admin/Polls/polls';
$Index->cancel_button_back = true;
$Index->content(
	h::h2($L->editing_of_poll($poll['title'])).
	h::{'input[is=cs-input-text][name=edit[title]]'}(
		[
			'value'       => $poll['title'],
			'placeholder' => $L->poll_title
		]
	).
	h::p(
		array_map(
			function ($option) {
				return h::{'input[is=cs-input-text]'}(
					[
						'value' => $option['title'],
						'name'  => "edit[options][$option[id]]"
					]
				);
			},
			$Options->get($Options->get_all_for_poll($poll['id']))
		)
	).
	h::{'input[type=hidden][name=edit[id]]'}(
		[
			'value' => $poll['id']
		]
	)
);
