<?php
/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Comments;
use
	cs\Cache,
	cs\Config,
	cs\Event;

Event::instance()
	->on(
		'Comments/instance',
		function ($data) {
			if (!Config::instance()->module('Comments')->enabled()) {
				return true;
			}
			$data['Comments'] = Comments::instance();
			return false;
		}
	)
	->on(
		'admin/System/components/modules/uninstall/before',
		function ($data) {
			if ($data['name'] != 'Comments') {
				return;
			}
			time_limit_pause();
			Cache::instance()->del('Comments');
			time_limit_pause(false);
		}
	);
