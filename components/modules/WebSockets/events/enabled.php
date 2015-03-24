<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\WebSockets;
use
	cs\Event;
Event::instance()
	->on('System/Session/del/after', function ($data) {
		Server::instance()->close_by_session($data['id']);
	})
	->on('System/Session/del_all', function ($data) {
		Server::instance()->close_by_user($data['id']);
	});
