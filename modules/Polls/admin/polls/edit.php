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
	cs\Request,
	h;

$Page    = Page::instance();
$L       = new Prefix('polls_');
$poll    = Polls::instance()->get(Request::instance()->route_ids[0]);
$Options = Options::instance();
$Page
	->title($L->editing_of_poll($poll['title']))
	->content(
		h::{'form[is=cs-form][action=admin/Polls/polls]'}(
			h::h2($L->editing_of_poll($poll['title'])).
			h::{'cs-input-text input[name=edit[title]]'}(
				[
					'value'       => $poll['title'],
					'placeholder' => $L->poll_title
				]
			).
			h::p(
				array_map(
					function ($option) {
						return h::{'cs-input-text input'}(
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
			).
			h::p(
				h::cs_button(
					h::{'button[type=submit]'}($L->save),
					[
						'tooltip' => $L->save_info
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
