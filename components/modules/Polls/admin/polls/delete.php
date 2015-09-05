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

$Index                     = Index::instance();
$Index->buttons            = false;
$Index->cancel_button_back = true;
$L                         = new Prefix('polls_');
$poll                      = Polls::instance()->get(Route::instance()->ids[0]);
Page::instance()->title(
	$L->deleting_of_poll($poll['title'])
);
$Index->action = 'admin/Polls/polls';
$Index->content(
	h::h2($L->deleting_of_poll($poll['title'])).
	h::{'button[is=cs-button][type=submit][name=delete]'}(
		$L->yes,
		[
			'value' => $poll['id']
		]
	)
);
