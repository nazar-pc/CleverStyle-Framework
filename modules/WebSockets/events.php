<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\WebSockets;
use
	cs\Config,
	cs\Event;

require_once __DIR__.'/functions.php';

Event::instance()
	->on(
		'System/Session/del/after',
		function ($data) {
			if (Config::instance()->module('WebSockets')->enabled()) {
				Server::instance()->close_by_session($data['id']);
			}
		}
	)
	->on(
		'System/Session/del_all',
		function ($data) {
			if (Config::instance()->module('WebSockets')->enabled()) {
				Server::instance()->close_by_user($data['id']);
			}
		}
	)
	->on(
		'admin/System/modules/install/after',
		function ($data) {
			if ($data['name'] != 'WebSockets') {
				return;
			}
			Config::instance()->module('WebSockets')->set(
				[
					'security_key'   => hash('sha224', random_bytes(100)),
					'listen_port'    => 8080,
					'listen_locally' => 1,
					'dns_server'     => '127.0.0.1'
				]
			);
		}
	);
