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
	cs\Cache,
	cs\Event,
	cs\User;

Event::instance()->on(
	'admin/System/components/modules/uninstall/before',
	function ($data) {
		if ($data['name'] != 'Polls' || !User::instance()->admin()) {
			return;
		}
		time_limit_pause();
		$Polls = Polls::instance();
		foreach ($Polls->get_all() ?: [] as $poll) {
			$Polls->del($poll);
		}
		unset(Cache::instance()->polls);
		time_limit_pause(false);
	}
);
