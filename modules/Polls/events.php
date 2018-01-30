<?php
/**
 * @package  Polls
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Polls;
use
	cs\Cache,
	cs\Event;

Event::instance()->on(
	'admin/System/modules/uninstall/before',
	function ($data) {
		if ($data['name'] != 'Polls') {
			return;
		}
		time_limit_pause();
		$Polls = Polls::instance();
		foreach ($Polls->get_all() ?: [] as $poll) {
			$Polls->del($poll);
		}
		Cache::instance()->del('polls');
		time_limit_pause(false);
	}
);
