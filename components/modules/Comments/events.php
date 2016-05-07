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
	cs\Event;

Event::instance()
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
	)
	->on(
		'Comments/deleted',
		function ($data) {
			Comments::instance()->del_all($data['module'], $data['item']);
		}
	);
