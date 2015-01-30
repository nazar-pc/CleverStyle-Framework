<?php
/**
 * @package        Polls
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Polls;

use
	cs\Cache,
	cs\Config,
	cs\DB,
	cs\Event,
	cs\User;

Event::instance()->on(
	'admin/System/components/modules/uninstall/process',
	function ($data) {
		if ($data['name'] != 'Polls' || !User::instance()->admin()) {
			return;
		}
		time_limit_pause();
		$Polls     = Polls::instance();
		$all_polls = $Polls->get_all();
		foreach ($all_polls as $poll) {
			$Polls->del($poll);
		}
		unset(
			$all_polls,
			$poll,
			Cache::instance()->polls
		);
		time_limit_pause(false);
	}
);
